<?php
/* 渠道订单*/

namespace Modules\Agent\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Models\AdminPlatform\OrderLog;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelWithdrawRecord;
use Transformers\ChannelWithdrawRecordTransformer;
use Validator;

class ChannelWithdrawController extends BaseController
{
    /**
     * 渠道订单列表
     *
     * @return Response
     */
    public function withdrawList()
    {
        Validator::make(request()->all(), [
            'channel_id' => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(ChannelWithdrawRecord::STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'channel_id.numeric' => 'channel_id必须数字',
            'status.numeric'     => '订单状态必须数字',
            'start_date.date'    => '无效日期',
            'end_date.date'      => '无效日期',
        ])->validate();
        $data = ChannelWithdrawRecord::andFilterWhere('status', request('status'))
            ->andFilterWhere('channel_id', request('channel_id'))
            ->andFilterWhere('status', request('status'))
            ->andFilterWhere('order_no', request('order_no'));
        if (2 == request()->input('time_type', 1)) {
            $data->where('status', ChannelWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'))
                ->orderby('updated_at', 'desc');
        } else {
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'))
                ->orderby('created_at', 'desc');
        }
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list              = $data->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('channel_id')->count('channel_id');
        $count['moneys']   = $statistics_coins->sum('value');
        return $this->response->paginator($list, new ChannelWithdrawRecordTransformer())->addMeta('status', ChannelWithdrawRecord::STATUS)
            ->addMeta('count', $count);
    }

    /**
     * 渠道订单列表（财务订单）
     *
     * @return Response
     */
    public function financeList()
    {
        Validator::make(request()->all(), [
            'channel_id' => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(ChannelWithdrawRecord::FINANCE_STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'channel_id.numeric' => 'channel_id必须数字',
            'status.numeric'     => '订单状态必须数字',
            'start_date.date'    => '无效日期',
            'end_date.date'      => '无效日期',
        ])->validate();
        $data = ChannelWithdrawRecord::whereIn('status', array_keys(ChannelWithdrawRecord::FINANCE_STATUS))
            ->andFilterWhere('status', request('status'))
            ->andFilterWhere('channel_id', request('channel_id'))
            ->andFilterWhere('order_no', request('order_no'));
        if (2 == request()->input('time_type', 1)) {
            $data->where('status', ChannelWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'))
                ->orderby('updated_at', 'desc');
        } else {
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'))
                ->orderby('created_at', 'desc');
        }
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list              = $data->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('channel_id')->count('channel_id');
        $count['moneys']   = $statistics_coins->sum('value');
        return $this->response->paginator($list, new ChannelWithdrawRecordTransformer())->addMeta('status', ChannelWithdrawRecord::FINANCE_STATUS)
            ->addMeta('count', $count);
    }

    /**
     * 客服审核通过
     *
     */
    public function withdrawPass()
    {
        try {
            $order = ChannelWithdrawRecord::find(request('id'));
        } catch (\ErrorException $exception) {
            return ResponeFails('订单查找不到');
        }
        if ($order->status != ChannelWithdrawRecord::WAIT_PROCESS) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $order->status   = ChannelWithdrawRecord::CHECK_PASSED;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        if (!$order->save()) {
            return ResponeFails('操作失败');
        }
        OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并通过', $order->order_no, '渠道'.config('set.withdrawal').'订单');
        return ResponeSuccess('审核成功.');
    }

    /**
     * 客服审核拒绝
     *
     */
    public function withdrawRefuse()
    {
        try {
            $order = ChannelWithdrawRecord::find(request('id'));
        } catch (\ErrorException $exception) {
            return ResponeFails('订单查找不到');
        }
        if ($order->status != ChannelWithdrawRecord::WAIT_PROCESS) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $order->status   = ChannelWithdrawRecord::CHECK_FAILS;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        $db_agent        = \DB::connection('agent');
        $db_agent->beginTransaction();
        try {
            $res = $order->save();
            //dd($res);
            $res2 = ChannelInfo::where('channel_id', $order->channel_id)->increment('balance', moneyToCoins($order->value));
            if ($res && $res2) {
                $db_agent->commit();
                OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并不通过,'.config('set.amount').'返还', $order->order_no, '渠道'.config('set.withdrawal').'订单');
            } else {
                $db_agent->rollback();
                return ResponeFails('操作失败');
            }
        } catch (\ErrorException $exception) {
            $db_agent->rollback();
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('审核成功,渠道'.config('set.amount').'已返还');
    }

    /**
     * 财务审核通过
     *
     */
    public function financePass()
    {
        try {
            $order = ChannelWithdrawRecord::find(request('id'));
        } catch (\ErrorException $exception) {
            return ResponeFails('订单查找不到');
        }
        if ($order->status != ChannelWithdrawRecord::CHECK_PASSED) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $order->status   = ChannelWithdrawRecord::PAY_SUCCESS;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        if (!$order->save()) {
            return ResponeFails('操作失败');
        }
        OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并通过', $order->order_no, '渠道'.config('set.withdrawal').'订单');
        return ResponeSuccess('审核成功.');
    }

    /**
     * 财务审核拒绝
     *
     */
    public function financeRefuse()
    {
        try {
            $order = ChannelWithdrawRecord::find(request('id'));
        } catch (\ErrorException $exception) {
            return ResponeFails('订单查找不到');
        }
        if ($order->status != ChannelWithdrawRecord::CHECK_PASSED) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $order->status   = ChannelWithdrawRecord::PAY_FAILS;
        $order->admin_id = $this->admin_id();
        $order->remark   = request('remark','');
        $db_agent        = \DB::connection('agent');
        $db_agent->beginTransaction();
        try {
            $res  = $order->save();
            $res2 = ChannelInfo::where('channel_id', $order->channel_id)->increment('balance', moneyToCoins($order->value));
            if ($res && $res2) {
                $db_agent->commit();
                OrderLog::addLogs($this->user()->username . '审核了订单:' . $order->order_no . '并不通过,'.config('set.amount').'返还', $order->order_no, '渠道'.config('set.withdrawal').'订单');
            } else {
                $db_agent->rollback();
                return ResponeFails('操作失败');
            }
        } catch (\ErrorException $exception) {
            $db_agent->rollback();
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('审核成功,渠道'.config('set.amount').'已返还');
    }

}
