<?php

namespace Modules\Channel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Models\Accounts\AccountsInfo;
use Models\Agent\ChannelIncome;
use Models\Agent\ChannelUserRelation;
use Models\Agent\ChannelInfo;
use Models\Treasure\RecordScoreDaily;
use Modules\Channel\Http\Requests\AddChannelRequest;
use Modules\Channel\Http\Requests\CheckWithdrawRequest;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\ChannelWithdrawRecord;
use Transformers\ChannelInfoTransformer;
use Transformers\ChannelUserRelationTransformer;
use Transformers\ChannelWithdrawRecordTransformer;
use Transformers\mySpreadChannelTransformer;
use Validator;

class ChannelController extends BaseController
{
    /**
     * 渠道首页-推广人数（折线图）
     * @return Response
     */
    // 折线图数据
    public function channel_spread_sum(Request $request)
    {
        //所有的下级渠道的推广人数，按查询日期统计(折线图)
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $channel_id  = $this->channel_id();//登录渠道后台的渠道id
        //通过渠道id集合获取渠道下面的用户集合
        $user_id = ChannelUserRelation::select(
                DB::raw("CONVERT(varchar(100), created_at, 23) as create_time"),
                DB::raw("count(user_id) as count_spread")
            )
            ->andFilterBetweenWhere('created_at', $start_date,$end_date)
            ->where('channel_id',$channel_id)
            ->groupBy(DB::raw("CONVERT(varchar(100), created_at, 23)"))
            ->pluck('count_spread','create_time');
        $r = $user_id->toArray();
        $user_ids = [];
        foreach ($dates as $key => $value){
            $user_ids[$value] = 0;
            if(isset($r[$value])){
                $user_ids[$value] = $r[$value];
            }
        }
        return ResponeSuccess('请求成功',$user_ids);
    }
    /**
     * 渠道首页-充值人数（折线图）
     * @return Response
     */
    // 折线图数据
    public function channel_people_sum(Request $request)
    {
        //所有的下级渠道的充值人数，按查询日期统计(折线图)
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $channel_id  = $this->channel_id();//登录渠道后台的渠道id
        $people_sums = PaymentOrder::from("payment_orders as a")
            ->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.user_id','=','b.user_id')
            ->select(
                DB::raw("CONVERT(varchar(100), a.created_at, 23) as create_time"),
                DB::raw('count(distinct(a.user_id)) as count_people')
            )
            ->where('a.payment_status',PaymentOrder::SUCCESS)
            ->where('b.channel_id',$channel_id)
            ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), a.created_at, 23)"))
            ->pluck('a.count_people','a.create_time')->toArray();
        $people_sum = [];
        foreach ($dates as $key => $value){
            $people_sum[$value] = 0;
            if(isset($people_sums[$value])){
                $people_sum[$value] = $people_sums[$value];
            }
        }
        return ResponeSuccess('请求成功',$people_sum);
    }
    /**
     * 渠道首页-充值量（折线图）
     * @return Response
     */
    // 折线图数据
    public function channel_recharge_sum(Request $request)
    {
        //所有的下级渠道的充值量，按查询日期统计(折线图)
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $channel_id  = $this->channel_id();//登录渠道后台的渠道id
        $recharge_sums = PaymentOrder::from("payment_orders as a")
            ->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.user_id','=','b.user_id')
            ->select(
                DB::raw("CONVERT(varchar(100), a.created_at, 23) as create_time"),
                DB::raw("SUM(a.amount) as sum_amount")
            )
            ->where('a.payment_status',PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
            ->where('b.channel_id',$channel_id)
            ->groupBy(DB::raw("CONVERT(varchar(100), a.created_at, 23)"))
            ->pluck('a.sum_amount','a.create_time')->toArray();
        $recharge_sum = [];
        foreach ($dates as $key => $value){
            $recharge_sum[$value] = 0;
            if(isset($recharge_sums[$value])){
                $recharge_sum[$value] = $recharge_sums[$value];
            }
        }
        return ResponeSuccess('请求成功',$recharge_sum);
    }
    /**
     * 渠道首页- 输赢（折线图）
     * @return Response
     */
    // 折线图数据
    public function channel_taxation_sum(Request $request)
    {
        //所有的下级渠道的推广的玩家输赢，按查询日期统计(折线图)
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $channel_id  = $this->channel_id();//登录渠道后台的渠道id
        $taxation_sums = RecordScoreDaily::from("RecordScoreDaily as a")
            ->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.UserID','=','b.user_id')
            ->select(
                DB::raw("a.UpdateDate as create_time") ,
                DB::raw("SUM(a.ChangeScore) as sum_value")
            )
            ->andFilterBetweenWhere('UpdateDate',$start_date,$end_date)
            ->where('b.channel_id',$channel_id)
            ->groupBy('a.UpdateDate')
            ->pluck('a.sum_value','a.create_time')->toArray();
        $taxation_sum = [];
        foreach ($dates as $key => $value){
            $taxation_sum[$value] = 0;
            if(isset($taxation_sums[$value])){
                $taxation_sum[$value] = -(realCoins($taxation_sums[$value]));
            }
        }
        return ResponeSuccess('请求成功',$taxation_sum);
    }
    /**
     * 渠道首页-流水（折线图）
     * @return Response
     */
    // 折线图数据
    public function channel_bets_sum(Request $request)
    {
        //所有的下级渠道的下注额，按查询日期统计(折线图)
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $channel_id  = $this->channel_id();//登录渠道后台的渠道id
        $bets_sums = RecordScoreDaily::from("RecordScoreDaily as a")
            ->leftJoin('AgentDB.dbo.channel_user_relation AS b','a.UserID','=','b.user_id')
            ->select(
                DB::raw("a.UpdateDate as update_time"),
                DB::raw("SUM(a.StreamScore) as sum_stream_score")
            )
            ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date)
            ->where('b.channel_id',$channel_id)
            ->groupBy('UpdateDate')
            ->pluck('sum_stream_score','update_time')->toArray();
        $bets_sum = [];
        foreach ($dates as $key => $value){
            $bets_sum[$value] = 0;
            if(isset($bets_sums[$value])){
                $bets_sum[$value] = realCoins($bets_sums[$value]);
            }
        }
        return ResponeSuccess('请求成功',$bets_sum);
    }

    /**
     * 我的推广
     * @return Response
     */
    // 输赢（单位）修改
    public function my_spread_channel(Request $request)
    {
        //只查当前渠道推广的玩家信息
        /* $request->validate([
            'game_id' => 'nullable|numeric',
        ]);*/
        Validator::make(request()->all(), [
            'game_id'         => ['nullable','numeric'],
        ], [
            'game_id.numeric' => '游戏id必须是数字，请重新输入！',
        ])->validate();
        $account='';
        if($request->input('game_id')){
            $account=AccountsInfo::where('GameID',$request->input('game_id'))->first();
        }
        $request_id = $this->channel_id();//登录的渠道的id
        $users_list = ChannelUserRelation::with(['gameScore:UserID,WinScore','paymentOrderSumAmount'=>function($query){
            $query->select(['user_id',DB::raw('SUM(amount) as amount')])->where('payment_status', PaymentOrder::SUCCESS)->groupBy('user_id');
        },
            'withdrawalOrderSumAmount'=>function($query){
                $query->select(['user_id',DB::raw('SUM(money) as real_money')])->where('status', WithdrawalOrder::PAY_SUCCESS)->groupBy('user_id');
            }
        ])
            ->where('channel_id',$request_id)
            ->andFilterWhere('user_id',$account->UserID ?? '')
            ->andFilterBetweenWhere('created_at',request('start_date'),request('end_date'))
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($users_list,new mySpreadChannelTransformer());
    }
    /**
     * 子渠道管理
     * @return Response
     */
    // 金币转货币
    public function next_channel_list(Request $request)
    {
        Validator::make(request()->all(), [
            'channel_id'         => ['nullable','numeric'],
        ], [
            'channel_id.numeric' => '渠道ID必须是数字，请重新输入！',
        ])->validate();
        //下级渠道列表，只查子渠道
        $channel_id = $this->channel_id();//登录的渠道的id
        //查询条件
        $map['parent_id'] = $channel_id;//查询自己的直属子集渠道
        $map['channel_id'] = request('channel_id');//渠道id检索
        //查询渠道列表
        $list = ChannelInfo::multiWhere($map)->andFilterBetweenWhere('created_at',request('start_date'),request('end_date'))->paginate(config('page.list_rows'));
        //return $list;
        //查子渠道的统计数据（充值，税收，收益，充值人数，税收返利）
        foreach ($list as $k => $v){
            $channelUser = new ChannelUserRelation();
            $user_ids = $channelUser->where('channel_id',$v['channel_id'])->pluck('user_id');//通过渠道id集合获取渠道和子渠道的用户集合
            $channel =RecordScoreDaily ::whereIn('UserID',$user_ids)
                ->select(
                    DB::raw('sum(ChangeScore) as ChangeScore'),//输赢（损益）
                    DB::raw('sum(StreamScore) as StreamScore') //流水
                )
                ->first();
            $pay = PaymentOrder::where('payment_status', PaymentOrder::SUCCESS)->whereIn('user_id', $user_ids)->sum('amount');
            $withdrawal = WithdrawalOrder::where('status', WithdrawalOrder::PAY_SUCCESS)->whereIn('user_id', $user_ids)->sum('money');
            $recharge = PaymentOrder::where('payment_status', PaymentOrder::SUCCESS)->whereIn('user_id', $user_ids)->distinct('user_id')->count('user_id');
            $list[$k]['winlose_sum'] = -(realCoins($channel -> ChangeScore)) ?? 0; //统计总输赢
            $list[$k]['stream_score'] = realCoins($channel -> StreamScore) ?? 0 ;//统计总流水
            $list[$k]['balance_sum'] = realCoins($v -> balance) ?? 0; //佣金余额
            $list[$k]['people_sum'] = count($user_ids); //统计总推广人数
            $list[$k]['pay_sum'] = $pay ?? 0; //统计总充值
            $list[$k]['withdraw_sum'] = $withdrawal ?? 0;
            $list[$k]['recharge_sum'] = $recharge ?? 0; //统计总充值人数
            $list[$k]['spread_domain'] = $v['channel_domain'].'?channelid='.$v['channel_id']; //推广域名
        }
        return $this->response->paginator($list,new ChannelInfoTransformer());
    }
    /**
     * 获取本渠道的返利方式和返利比例
     * @return Response
     */
    public function channel_return_type()
    {
        $channel_id=$this->channel_id();
        $result=ChannelInfo::find($channel_id);
        return ResponeSuccess('请求成功',$result);
    }
    /**
     * 渠道记录
     * @return Response
     */
    public function channel_withdraw_list(Request $request)
    {
        //按日期查询
        $id = $this->channel_id();//登录的渠道id
        $list = ChannelWithdrawRecord::where('channel_id',$id)
            ->andFilterBetweenWhere('created_at',request('start_date'),request('end_date'))
            ->orderBy('created_at','desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new ChannelWithdrawRecordTransformer());
    }
    /**
     * 渠道
     * @return Response
     */
    public function channel_withdraw(CheckWithdrawRequest $request)
    {
        //验证提交和渠道id
        $id = $this->channel_id();//登录的渠道id
        if(!$id){
            return ResponeFails('没有渠道id');
        }
        $model=ChannelInfo::find($id);
        if(!$model){
            return ResponeFails('没有找到该渠道');
        }
        $now_balance = $model-> balance;
        $withdraw_value = moneyToCoins($request->input('value')); // 货币转成金币
        if($now_balance < $withdraw_value){
            return ResponeFails(config('set.withdrawal').config('set.amount').'大于余额，'.config('set.withdrawal').'失败');
        }
        DB::beginTransaction();
        try {
            //减少渠道金币数
            $model->balance -= $withdraw_value;
            $res = $model->save();
            //生成记录
            $request_params = new ChannelWithdrawRecord(); //记录表
            $request_params -> channel_id = $this->channel_id();//登录的渠道id
            $request_params -> order_no = date('YmdHis',time()).rand(1000,9999);//随机生成一个订单号
            $request_params -> card_no = $request->input('card_no');
            $request_params -> bank_info = $request->input('bank_info');
            $request_params -> payee = $request->input('payee');
            $request_params -> phone = $request->input('phone');
            $request_params -> value = $request->input('value');
            $res2 = $request_params->save();
            if ($res && $res2) {
                DB::commit();
                return ResponeSuccess('操作成功');
            }
        }catch (\Exception $e) {
            DB::rollback();
            return ResponeFails('操作失败');
        }
    }
    /**
     * 渠道额度
     * @return Response
     */
    // 金币转货币
    public function channel_withdraw_quota(Request $request)
    {
        $id = $this->channel_id();  //渠道id
        $res = ChannelInfo::where('channel_id',$id)->first();
        $balance =realCoins($res['balance']); // 金币转货币
        return ResponeSuccess('请求成功',$balance);
    }
    /**
     * 渠道查最新的账号信息
     * @return Response
     */
    public function channel_withdraw_account(Request $request)
    {
        $id = $this->channel_id();//渠道id
        $list = ChannelWithdrawRecord::where('channel_id',$id)->orderby('created_at','desc')->first();
        return ResponeSuccess('请求成功',$list);
    }

}
