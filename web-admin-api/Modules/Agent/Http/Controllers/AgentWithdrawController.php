<?php

namespace Modules\Agent\Http\Controllers;

use App\Jobs\SendMailUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\OrderLog;
use Models\Agent\AgentInfo;
use Models\Agent\AgentWithdrawRecord;
use Transformers\AgentWithdrawRecordTransformer;
use Validator;

class AgentWithdrawController extends BaseController
{
    /**
     * 代理订单列表
     * @return Response
     */
    public function withdrawList()
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(AgentWithdrawRecord::STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric'        => '代理ID必须数字',
            'status.numeric'         => '订单状态必须数字',
            'start_date.date'        => '无效日期',
            'end_date.date'          => '无效日期',
        ])->validate();
        $data = $this->gameIdSearchUserId(request('game_id'), new AgentWithdrawRecord(),'user_id')
            ->andFilterWhere('status', request('status'))
            ->andFilterWhere('order_no', request('order_no'));
        if (2 == request()->input('time_type',1)){
            $data->where('status',AgentWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'));
        }else{
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'));
        }
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list = $data->orderBy('id','desc')->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('user_id')->count('user_id');
        $count['moneys']   = realCoins($statistics_coins->sum('score'));
        return $this->response->paginator($list,new AgentWithdrawRecordTransformer())->addMeta('status',AgentWithdrawRecord::STATUS)
            ->addMeta('count',$count);
    }

    /**
     * 代理财务订单列表
     * @return Response
     */
    public function financeList()
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(AgentWithdrawRecord::FINANCE_STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric'        => '代理ID必须数字',
            'status.numeric'         => '订单状态必须数字',
            'start_date.date'        => '无效日期',
            'end_date.date'          => '无效日期',
        ])->validate();

        $data = $this->gameIdSearchUserId(request('game_id'), new AgentWithdrawRecord(),'user_id')
            ->whereIn('status', array_keys(AgentWithdrawRecord::FINANCE_STATUS))
            ->andFilterWhere('status', request('status'))
            ->andFilterWhere('order_no', request('order_no'));
        if (2 == request()->input('time_type',1)){
            $data->where('status',AgentWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'));
        }else{
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'));
        }
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list = $data->orderBy('id','desc')->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('user_id')->count('user_id');
        $count['moneys']   = realCoins($statistics_coins->sum('score'));
        return $this->response->paginator($list,new AgentWithdrawRecordTransformer())->addMeta('status',AgentWithdrawRecord::FINANCE_STATUS)
            ->addMeta('count',$count);
    }

    /**
     * 客服审核通过
     *
     */
    public function withdrawPass()
    {
        try{
            $order = AgentWithdrawRecord::find(request('id'));
        }catch (\ErrorException $exception){
            return ResponeFails('订单查找不到');
        }
        if ($order->status != AgentWithdrawRecord::WAIT_PROCESS) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }

        $order->status   = AgentWithdrawRecord::CHECK_PASSED;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        if (!$order->save()){
            return ResponeFails('操作失败');
        }
        OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并通过', $order->order_no, '代理'.config('set.withdrawal').'订单');
        return ResponeSuccess('审核成功');
    }

    /**
     * 客服审核拒绝
     *
     */
    public function withdrawRefuse()
    {
        try{
            $order = AgentWithdrawRecord::find(request('id'));
        }catch (\ErrorException $exception){
            return ResponeFails('订单查找不到');
        }
        if ($order->status != AgentWithdrawRecord::WAIT_PROCESS) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $order->status   = AgentWithdrawRecord::CHECK_FAILS;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        $db_agent        = \DB::connection('agent');
        $db_agent->beginTransaction();
        try{
            $res = $order->save();
            $res2 = AgentInfo::where('user_id',$order->user_id)->increment('balance', $order->score);
            //审核备注，发送邮件
            $game_id = AccountsInfo::where('UserID',$order->user_id)->value('GameID');
            $sendType = request('send_type');
            if ($res && $res2){
                $db_agent->commit();
                if($sendType==1) //发送邮件
                {
                    $data=[];
                    $data['GameIDs']= [$game_id];
                    $data['SendType']='1';   // 按玩家发送
                    $data['Title']='代理订单客服审核订单-取消订单';  //邮件标题
                    $data['Context']=request('remark');  //邮件内容
                    $data['TimeType']='1';  //发送类型：1、立即发送，2、定时发送
                    $data['admin_id']=$this->user()->id;   //发送人
                    $res3 = SendMailUser::dispatch($data);
                    if ($res3){
                        return ResponeSuccess('发送成功');
                    }else{
                        return ResponeFails('发送失败');
                    }
                }
                OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并不通过,金币返还', $order->order_no, '代理'.config('set.withdrawal').'订单');
            }else{
                $db_agent->rollback();
                return ResponeFails('操作失败');
            }
        }catch (\ErrorException $exception){
            $db_agent->rollback();
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('操作成功 ,用户金币已返还');
    }

    /**
     * 财务审核通过
     *
     */
    public function financePass()
    {
        try{
            $order = AgentWithdrawRecord::find(request('id'));
        }catch (\ErrorException $exception){
            return ResponeFails('订单查找不到');
        }
        if ($order->status != AgentWithdrawRecord::CHECK_PASSED) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $order->status   = AgentWithdrawRecord::PAY_SUCCESS;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        if (!$order->save()){
            return ResponeFails('操作失败');
        }
        OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并通过',$order->order_no, '代理'.config('set.withdrawal').'订单');
        return ResponeSuccess('审核成功');
    }

    /**
     * 财务审核拒绝
     *
     */
    public function financeRefuse()
    {
        try{
            $order = AgentWithdrawRecord::find(request('id'));
        }catch (\ErrorException $exception){
            return ResponeFails('订单查找不到');
        }
        if ($order->status != AgentWithdrawRecord::CHECK_PASSED) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $order->status   = AgentWithdrawRecord::PAY_FAILS;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        $db_agent        = \DB::connection('agent');
        $db_agent->beginTransaction();
        try{
            $res = $order->save();
            $res2 = AgentInfo::where('user_id',$order->user_id)->increment('balance', $order->score);
            //审核备注，发送邮件
            $game_id  = AccountsInfo::where('UserID',$order->user_id)->value('GameID');
            $sendType = request('send_type');
            if ($res && $res2){
                $db_agent->commit();
                if($sendType==1) //发送邮件
                {
                    $data = [];
                    $data['GameIDs'] = [$game_id];
                    $data['SendType'] = '1';   // 按玩家发送
                    $data['Title'] = '代理订单财务审核订单-取消订单';  //邮件标题
                    $data['Context'] = request('remark');  //邮件内容
                    $data['TimeType'] = '1';  //发送类型：1、立即发送，2、定时发送
                    $data['admin_id'] = $this->user()->id;   //发送人
                    try {
                        $res3 = SendMailUser::dispatch($data);
                        if ($res3) {
                            return ResponeSuccess('发送成功');
                        } else {
                            return ResponeFails('发送失败');
                        }
                    } catch (\Exception $e) {
                        return ResponeFails('订单修改成功，发送邮件失败');
                    }
                }
                OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并不通过,金币返还',$order->order_no, '代理'.config('set.withdrawal').'订单');
            }else{
                $db_agent->rollback();
                return ResponeFails('操作失败');
            }
        }catch (\ErrorException $exception){
            $db_agent->rollback();
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('审核成功,用户金币已返还');
    }

}
