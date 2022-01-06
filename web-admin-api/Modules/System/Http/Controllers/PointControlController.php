<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\PointControl;
use Modules\System\Http\Requests\PointControlRequest;
use Transformers\PointControlTransformer;
use Validator;


class PointControlController extends Controller
{
    //点控设置列表
    public function getList(Request $request)
    {
        Validator::make($request->all(), [
            'game_id'       => ['nullable','integer'],
            'control_type'  => ['nullable','in:1,2'],
            'priority'      => ['nullable','in:1,2,3,4'],
        ], [
            'game_id.integer' => '目标id必须是整数',
            'control_type.in' => '控制方式不在可选范围内',
            'priority.in'     => '执行优先级不在可选范围内',
        ])->validate();
        $list = PointControl::where('status',PointControl::NORMAL)
            ->andFilterWhere('game_id',request('game_id'))
            ->andFilterWhere('type',request('control_type'))
            ->andFilterWhere('priority',request('priority'))
            ->orderBy('created_at','desc')
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new PointControlTransformer())->addMeta('control_type',PointControl::CONTROL_TYPE)->addMeta('priority',PointControl::PRIORITY);
    }
    //点控设置保存
    public function pointControlAdd(PointControlRequest $request){
        $model = new PointControl();
        $is_exit = PointControl::where('game_id',request('game_id'))->where('status',PointControl::NORMAL)->count();
        if($is_exit > 0){
            return ResponeFails('该目标id点控配置已存在');
        }
        $is_limit_exceeded = PointControl::where('status',PointControl::NORMAL)->count();
        if($is_limit_exceeded >= 10){
            return ResponeFails('点控配置超出限制条数');
        }
        if(request('control_type') == PointControl::FIXED_GOLD){
            if(empty(request('probability'))){
                return ResponeFails('获胜概率必填');
            }
        }else{
            if(empty(request('target'))){
                return ResponeFails('胜负目标必选');
            }
            if(request('number')<=0){
                return ResponeFails('目标局数必需大于0');
            }
        }
        $model->game_id     = $request->input('game_id');
        $model->type        = $request->input('control_type');
        $model->number      = request('control_type') == PointControl::FIXED_GOLD ? moneyToCoins($request->input('number')):$request->input('number');
        $model->priority    = $request->input('priority');
        $model->probability = $request->input('probability')*100 ?? 0;
        $model->target      = $request->input('target') ?? 0;
        $model->created_at  = date('Y-m-d H:i:s');
        if ($model->save()) {
            return ResponeSuccess('新增成功');
        }
        return ResponeFails('新增失败');
    }
    //点控设置删除
    public function pointControlDelete(Request $request){
        Validator::make($request->all(), [
            'id'          => ['required','integer'],
            'reason'      => ['required'],

        ], [
            'id.integer'      => 'id必传',
            'reason.required' => '删除理由必填',
        ])->validate();
        $model = PointControl::where('status',PointControl::NORMAL)->where('id',$request->input('id'))->first();
        if (!$model) {
            return ResponeFails('该条点控配置状态异常，无法删除');
        }
        $model->reason     = $request->input('reason');
        $model->status     = PointControl::HALFWAY_DELETE;
        $model->finished_at = date('Y-m-d H:i:s');
        if ($model->save()) {
            return ResponeSuccess('删除成功');
        }
        return ResponeFails('删除失败');
    }
    //点控设置-历史记录
    public function historicRecords(Request $request){
        Validator::make($request->all(), [
            'game_id'       => ['nullable','integer'],
            'start_date'    => ['nullable','date'],
            'end_date'      => ['nullable','date'],
            'status'        => ['nullable','in:1,2'],
        ], [
            'game_id.integer' => '目标id必须是整数',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
        ])->validate();
        $list = PointControl::where('status','<>',PointControl::NORMAL)
            ->andFilterWhere('game_id',request('game_id'))
            ->andFilterWhere('status',request('status'))
            ->andFilterBetweenWhere('finished_at', request('start_date'), request('end_date'))
            ->orderBy('finished_at','desc')
            ->paginate(config('page.list_rows'));
        $sum_winorlose = PointControl::where('status','>',PointControl::NORMAL)->sum('winorlose');
        return $this->response->paginator($list, new PointControlTransformer())->addMeta('sum_winorlose',realCoins($sum_winorlose))->addMeta('records_status',PointControl::RECORDS_STATUS);
    }

}
