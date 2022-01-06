<?php
/* 注册赠送 */
namespace Modules\Activity\Http\Controllers;

use App\Exceptions\NewException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\RegisterGive;
use Transformers\RegisterGiveTransformer;
use Validator;

class RegisterGiveController extends Controller
{
    /**
     * 注册赠送列表
     * @return Response
     */
    public function register_give_list()
    {
        $list=RegisterGive::paginate(config('page.list_rows'));
        return $this->response->paginator($list,new RegisterGiveTransformer());
    }
    /**
     * 注册赠送添加
     * @return Response
     */
    public function register_give_add(Request $request)
    {
        //生成注册赠送记录
        Validator::make($request->all(), [
            'score_count'         => ['required','numeric','min:0.01','max:666'],
            'score_max'           => ['required','numeric','min:0.01','max:666'],
            'give_type'           => ['required','in:1,2'],
            'platform_type'       => ['required','in:'.implode(',',RegisterGive::TYPE)]
        ], [
            'score_count.required'    => '赠送金币下限必填',
            'score_count.numeric'     => '赠送金币下限必须是数字',
            'score_count.min'         => '赠送金币下限必须大于0',
            'score_count.max'         => '赠送金币下限必须小于666',
            'score_max.required'      => '赠送金币上限必填',
            'score_max.numeric'       => '赠送金币上限必须是数字',
            'score_max.min'           => '赠送金币上限必须大于0',
            'score_max.max'           => '赠送金币上限必须小于666',
//            'score_max.gte'           => '赠送金币上限必须大于等于下限',
            'give_type.required'      => '赠送类型必填',
            'platform_type.required'  => '平台类型必填',
            'give_type.in'            => '赠送类型不在可选范围',
            'platform_type.in'        => '平台类型不在可选范围',
        ])->validate();
        if(!$this->validateScoreMax($request->score_count, $request->score_max)) {
            throw  new NewException('赠送金币上限必须大于等于下限');
        }
        $request_params = new RegisterGive();
        $res = $request_params->where('give_type',$request->input('give_type'))->where('platform_type',$request->input('platform_type'))->first();
        if($res){
            return ResponeFails('该配置已经存在');
        }
        $request_params -> score_count = moneyToCoins($request->input('score_count'));
        $request_params -> score_max = moneyToCoins($request->input('score_max'));
        $request_params -> platform_type = $request->input('platform_type');
        $request_params -> give_type = $request->input('give_type');
        if ($request_params->save()) {
            return ResponeSuccess('操作成功');
        }
        return ResponeFails('操作失败');

    }
    /**
     * 注册赠送编辑
     * @return Response
     */
    public function register_give_edit(Request $request)
    {
        Validator::make($request->all(), [
            'score_count'         => ['required','numeric','min:0.01','max:666'],
            'score_max'           => ['required','numeric','min:0.01','max:666'],
            'give_type'           => ['required'],
            'platform_type'       => ['required']
        ], [
            'score_count.required'    => '赠送金币下限必填',
            'score_count.numeric'     => '赠送金币下限必须是数字',
            'score_count.min'         => '赠送金币下限必须大于0',
            'score_count.max'         => '赠送金币下限必须小于666',
            'score_max.required'      => '赠送金币上限必填',
            'score_max.numeric'       => '赠送金币上限必须是数字',
            'score_max.min'           => '赠送金币上限必须大于0',
            'score_max.max'           => '赠送金币上限必须小于666',
//            'score_max.gte'           => '赠送金币上限必须大于等于下限',
            'give_type.required'      => '赠送类型必填',
            'platform_type.required'  => '平台类型必填',
        ])->validate();
        if(!$this->validateScoreMax($request->score_count, $request->score_max)) {
            throw  new NewException('赠送金币上限必须大于等于下限');
        }
        $id=$request->input('id','');
        if(!$id){
            return ResponeFails('没有id');
        }
        $model=RegisterGive::find($id);//注册赠送表
        if(!$model){
            return ResponeFails('没有找到id');
        }
        $model -> loadFromRequest();
        $model -> score_count = moneyToCoins($model -> score_count);
        $model -> score_max = moneyToCoins($model -> score_max);
        $res = RegisterGive::where('give_type',$request->input('give_type'))->where('platform_type',$request->input('platform_type'))->where('id','<>',$id)->first();
        if($res){
            return ResponeFails('该配置已经存在!');
        }
        if ($model->save()) {
            return ResponeSuccess('操作成功');
        }
        return ResponeFails('操作失败');
    }
    /**
     * 注册赠送删除
     * @return Response
     */
    public function register_give_delete(Request $request)
    {
        $ids = $request->input('ids','');
        $res = RegisterGive::whereIn('id',$ids)->delete();
        if ($res) {
            return ResponeSuccess('删除成功');
        }
        return ResponeFails('删除失败');
    }

    public function validateScoreMax($score_count, $score_max)
    {
        if($score_count > $score_max) {
            return false;
        }
        return true;
    }
}
