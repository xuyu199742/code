<?php

namespace Modules\System\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Models\Accounts\UserLevel;
use Models\AdminPlatform\PaymentPassageway;
use Models\AdminPlatform\PaymentProvider;
use Models\AdminPlatform\PaymentWay;
use Models\AdminPlatform\RechargeAgent;
use Models\AdminPlatform\RechargeAlipay;
use Models\AdminPlatform\RechargeUnion;
use Models\AdminPlatform\RechargeWechat;
use Validator;

class PaymentWaysController extends Controller
{
    /**
     * 充值方式配置列表
     */
    public function getRechargeWaysList(){
        $list = PaymentWay::where('type','>',0)->orderBy('sort', 'asc')->get();
        return ResponeSuccess('请求成功',$list);
    }
    /**
     * 官方充值通道配置列表
     */
    public function getOfficialWaysList(){
        $list = PaymentWay::where('type',0)->orderBy('sort', 'asc')->get();
        return ResponeSuccess('请求成功',$list);
    }
    /**
     * 充值方式配置保存
     */
    public function getRechargeWaysSave(Request $request){
        Validator::make($request->all(), [
            'name'          => ['required','max:30'],
            'status'        => ['required'],
            'marker'        => ['required'],
            'sort'          => ['required','numeric', 'min:1'],
        ], [
            'name.required'     => '充值方式必填',
            'status.required'   => '充值方式开启状态必选',
            'marker.required'   => '角标类型必选',
            'sort.required'     => '排序值不可为空',
            'sort.numeric'      => '排序值必须是数字',
            'sort.min'          => '排序值必须大于0',
        ])->validate();
        if ($request->input('id')) {
            $model = PaymentWay::find($request->input('id'));
            if (!$model) {
                return ResponeFails('充值方式配置不存在');
            }
        } else {
            $model = new PaymentWay();
        }
        $model->name   = $request->input('name');
        $model->status = $request->input('status', PaymentWay::OFF);
        $model->marker = $request->input('marker');
        $model->sort   = $request->input('sort', 0);
        if($model->type == 2) {   //官方充值
            $res1 = PaymentWay::whereIn('type', [0, 2])->update(['status' => $request->input('status')]);  //充值方式
        }
        if($model->save()){
            return ResponeSuccess('保存成功');
        }else{
            return ResponeFails('保存失败');
        }
    }
    /**
     * 获取单个通道配置
     */
    public function getRechargePassWaysList(Request $request){
        Validator::make($request->all(), [
            'id'            => ['required'],
            'table_type'    => ['required','in:1,2,3,4,5'],
        ], [
            'id.required'           => '充值通道id必传',
            'table_type.required'   => '充值通道类型必传',
            'table_type.in'         => '充值通道类型不在可选范围内',
        ])->validate();
        $id = Request('id');
        $data=[];
        $vip_lists= UserLevel::pluck('LevelName')->toArray();
        array_unshift($vip_lists, '未充值');
        $table_type = Request('table_type');
        $data = PaymentPassageway::where('pid', $id)->where('table_type', $table_type)->first();
        if($data){
            $i = 0;
            $auths = [];
            foreach ($vip_lists as $k => $v) {
                $auths[$v] = $data->authority & pow(2, $i) ? 1 : 0;
                $i++;
            }
            $data['auths'] = $auths;
        }else{
            $data['auths'] = $vip_lists;
        }
        return ResponeSuccess('请求成功',$data);
    }
    /**
     * 充值通道配置保存
     */
    public function getRechargePassWaysSave(Request $request){
        Validator::make($request->all(), [
            'pid'           => ['required'],
            'wid'           => ['required'],
            'table_type'    => ['required','in:1,2,3,4,5'],
            'name'          => ['required','max:30'],
            'sort'          => ['required','numeric', 'min:1'],
            'marker'        => ['required'],
            'status'        => ['required'],
            'auths'         => ['required'],
            'frequency'     => ['required','numeric', 'min:0', 'max:999'],
        ], [
            'pid.required'        => '对应表中的id必填',
            'table_type.required' => '对应的表必填',
            'name.required'       => '充值通道名称必填',
            'status.required'     => '充值通道开启状态必选',
            'marker.required'     => '角标类型必选',
            'sort.required'       => '排序值不可为空',
            'sort.numeric'        => '排序值必须是数字',
            'sort.min'            => '排序值必须大于0',
            'auths.required'      => '权限必传',
            'frequency.required'  => '次数不能为空',
            'frequency.numeric'   => '次数必须为0~999之间的整数',
            'frequency.min'       => '次数必须为0~999之间的整数',
            'frequency.max'       => '次数必须为0~999之间的整数',
        ])->validate();
        $pid        = $request->input('pid');
        $table_type = $request->input('table_type');
        $wid        = $request->input('wid');
        if ($request->input('id')) {
            $model = PaymentPassageway::find($request->input('id'));
            if (!$model) {
                return ResponeFails('充值通道配置不存在');
            }
        }else{
            $model = new PaymentPassageway();
        }
        $model->pid         = $pid;
        $model->table_type  = $table_type;
        $model->wid         = $wid;
        $model->name        = $request->input('name');
        $model->status      = $request->input('status', PaymentPassageway::OFF);
        $model->marker      = $request->input('marker');
        $model->sort        = $request->input('sort', 0) ;
        $model->frequency   = $request->input('frequency', 0) ;
        $auths = request('auths',[]);
        if(count($auths) != count(PaymentPassageway::VIP_LISTS)){
            return ResponeFails('传参有误');
        }
        $auth = 0;
        $i = 0;
        foreach ($auths as $k => $v){
            if ($v == 1){
                $auth += pow(2,$i);
            }
            $i++;
        }
        $model->authority = $auth;
        if($table_type==1){
            $model1=RechargeAgent::find($pid);
        }elseif ($table_type==2){
            $model1=RechargeUnion::find($pid);
        }
        elseif ($table_type==3){
            $model1=RechargeWechat::find($pid);
        }
        elseif ($table_type==4){
            $model1=RechargeAlipay::find($pid);
        }else{
            $model1=PaymentProvider::find($pid);
        }
        if(!$model1){
            return ResponeFails('充值通道不存在');
        }else{
            if($table_type==5){
                $model1->provider_name = $request->input('name');
                $model1->weight        = $request->input('sort',0);
                $model1->status        = $request->input('status');
            }else{
                $model1->name  = $request->input('name');
                $model1->sort  = $request->input('sort',0);
                $model1->state = $request->input('status')=='ON' ? 1:0;
            }
        }
        if ($model->save() && $model1->save()) {
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }
    /**
     * 四方充值通道删除
     */
    public function getRechargePassWaysDel(Request $request)
    {
        $res = PaymentPassageway::where('pid',$request->input('id'))->where('table_type',PaymentPassageway::PAYMENT_PROVIDERS)->delete();
        $result= PaymentProvider::where('id',$request->input('id'))->update(['status' => PaymentProvider::OFF]);
        if (!$res && !$result){
            return ResponeFails('删除失败');
        }
        return ResponeSuccess('删除成功');
    }

}
