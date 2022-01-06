<?php

namespace Modules\Channel\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\ChannelIncome;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Models\Treasure\RecordScoreDaily;


class ChannelCountController extends BaseController
{
    /**
     * 渠道后台渠道个人相关数据统计（用于统计渠道个人开发的玩家）
     *
     */
    // 渠道首页的统计渠道个人的数据，金币转货币
    public function channelCount()
    {
        $channel_id = $this->channel_id();//登录渠道后台的渠道id
        $balance= ChannelInfo::where('channel_id',$channel_id)->first();
        $channelUser = new ChannelUserRelation();
        $channel = RecordScoreDaily::from('RecordScoreDaily as a')->select(
                DB::raw('sum(a.ChangeScore) as ChangeScore'),    //输赢（损益）
                DB::raw('sum(a.JettonScore) as JettonScore'),    //投注
                DB::raw('sum(a.StreamScore) as StreamScore')     //流水
        )->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.UserID','=','b.user_id')
        ->where('b.channel_id',$channel_id)->first();

        $pay = PaymentOrder::from('payment_orders as a')
        ->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.user_id','=','b.user_id')
        ->where('b.channel_id',$channel_id)
        ->where('a.payment_status', PaymentOrder::SUCCESS)
        ->sum('a.amount');

        $withdrawal = WithdrawalOrder::from('withdrawal_orders as a')
        ->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.user_id','=','b.user_id')
        ->where('b.channel_id',$channel_id)
        ->where('status', WithdrawalOrder::PAY_SUCCESS)->sum('a.money');

        $people_count = $channelUser->where('channel_id',$channel_id)->count();//通过渠道id集合获取渠道下面的用户集合

        $today = date('Y-m-d',time());
        $res = RecordScoreDaily::from('RecordScoreDaily as a')->select(
            DB::raw('sum(a.ChangeScore) as ChangeScore')    //输赢（损益）
        )
        ->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.UserID','=','b.user_id')
        ->where('b.channel_id',$channel_id)
        ->where('a.UpdateDate', $today)
        ->first();

        $list = [
            'winlose_sum'    =>  -(realCoins($channel -> ChangeScore)) ?? 0,    //渠道推广的系统总输赢
            'bet_sum'        =>  realCoins($channel -> JettonScore) ?? 0,     //渠道推广的玩家下注
            'flowing_water'  =>  realCoins($channel -> StreamScore) ?? 0,     //流水
            'balance_sum'    =>  realCoins($balance['balance']) ?? 0,     //渠道的余额，金币转货币
            'people_sum'     =>  $people_count,    //渠道推广的玩家人数
            'pay_sum'        =>  $pay,   //渠道推广的玩家充值
            'withdraw_sum'   =>  $withdrawal,   //渠道推广的玩家
            'today_winlose_sum' =>-(realCoins($res -> ChangeScore)),//渠道的推广的玩家的今日总输赢
        ];
        return ResponeSuccess('获取成功',$list);
    }

    /**
     * 渠道后台整条线的相关数据总统计（用于统计渠道个人及所有子渠道开发的玩家）
     *
     */
    //渠道首页的统计渠道以及子渠道的数据,金币转货币
    public function channelAllCount()
    {
        $channel_id  = $this->channel_id();//登录渠道后台的渠道id
        $channel_ids = ChannelInfo::where('parent_id',$channel_id)->pluck('channel_id');
        $channel_ids[] = $channel_id ;
        $channelUser = new ChannelUserRelation();
        $user_ids = $channelUser->whereIn('channel_id',$channel_ids)->pluck('user_id');//通过渠道id集合获取渠道和子渠道的用户集合
        /*$channel = RecordScoreDaily::whereIn('UserID',$user_ids)
            ->select(
                \DB::raw('sum(ChangeScore) as ChangeScore'),//输赢（损益）
                \DB::raw('sum(SystemServiceScore) as SystemServiceScore')  //税收
            )
            ->first();
        $pay = PaymentOrder::where('payment_status','SUCCESS')->whereIn('user_id',$user_ids)->sum('amount');
        $withdrawal = WithdrawalOrder::where('status',WithdrawalOrder::PAY_SUCCESS)->whereIn('user_id',user_ids)->sum('money');*/
        //历史总佣金
        $balance_sum = ChannelIncome::where('channel_id',$channel_id)->sum('return_score');
        $list = [
            'balance_sum' => realCoins($balance_sum) ?? 0,  //统计总佣金余额
            'people_sum'  => count($user_ids),   //统计总推广人数
        ];
        return ResponeSuccess('获取成功',$list);
    }

}
