<?php

namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentConfig;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\PaymentProvider;
use Models\AdminPlatform\StatisticsBalance;
use Models\AdminPlatform\StatisticsGameDatas;
use Models\AdminPlatform\StatisticsOnlineData;
use Models\AdminPlatform\VipBusinessman;
use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentIncome;
use Models\Agent\AgentRelation;
use Models\Agent\AgentWithdrawRecord;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelWithdrawRecord;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;
use Models\OuterPlatform\OuterPlatform;
use Transformers\AccountBalanceListTransformer;
use Validator;

class SystemReportFormController extends Controller
{
    //报表信息
    public function getInfo()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'client_type'=> ['nullable','in:1,2,3'] //注册来源：1、android，2、ios，3、h5
        ], [
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'client_type.in'  => '注册来源不在可选范围内'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $client_type = request('client_type');
        $info = [
            'recharge_data'     => [],  //充值数据
            'withdrawal_data'   => [],
            'other'             => [],  //其他
            'profit_loss'       => [],  //盈亏和盈亏率
            'prize_score'       => [],  //派彩和中奖
            'people_num'        => [],  //注册人数,首充,在线人数,绑定人数,绑定率
            'cash_gift'         => [],  //活动礼金
            'channel_agent'     => [],  //渠道和代理
            'game_data'         => [],  //游戏数据
        ];
        /*中奖*/
        $prize_moneys = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(RecordGameScore::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                \DB::raw('SUM(b.JettonScore-b.RewardScore) as platform_profit'), //平台盈利=投注-中奖
                \DB::raw('SUM(b.JettonScore) as JettonScore'),   //投注
                \DB::raw('SUM(b.RewardScore) as RewardScore'),   //中奖
                \DB::raw('COUNT(DISTINCT b.UserID) as people_total')) //投注人数*
            ->where('a.IsAndroid', 0)
            ->when($client_type, function ($query) use ($client_type) {
                $query->where('a.ClientType', $client_type);
            })
            ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                $start_date = Carbon::parse($start_date)->format('Y-m-d 00:00:00');
                $end_date = Carbon::parse($end_date)->format('Y-m-d 23:59:59');
                $query->whereBetween('b.UpdateTime', [$start_date, $end_date]);
            })
            ->first();
        $info['prize_score']['prize_moneys'] = realCoins($prize_moneys['RewardScore'] ?? '0.00'); //中奖
        $info['prize_score']['platform_profit'] = realCoins($prize_moneys['platform_profit'] ?? '0.00');//平台盈利
        $info['prize_score']['jetton_score'] = realCoins($prize_moneys['JettonScore'] ?? '0.00'); //投注
        $info['prize_score']['people_total'] = $prize_moneys['people_total'] ?? 0; //投注人数
        //充值数据
        //充值人数，充值，充值笔数
        $data_recharge = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as people_total'),
                \DB::raw('COUNT(b.id) as num_total'))
            ->where('a.IsAndroid',0)
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();
        $info['recharge_data']['amount']=$data_recharge['amount'] ?? '0.00';  //充值
        $info['recharge_data']['people_total']=$data_recharge['people_total'] ?? 0; //充值人数
        $info['recharge_data']['num_total']=$data_recharge['num_total'] ?? 0; //充值笔数
        //内部充值
        $inner_keys=array_keys(PaymentOrder::OFFICIAL_KEYS);
        $official_pay = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as people_total')
            )
            ->where('a.IsAndroid',0)
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->whereIn('b.payment_type',$inner_keys)
            ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();
        $info['recharge_data']['official_pay'] = $official_pay['amount'] ?? '0.00';
        $info['recharge_data']['official_total'] = $official_pay['people_total'] ?? 0;
        //外部充值
        $third_pay = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as people_total')
            )
            ->where('a.IsAndroid',0)
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->andFilterWhere('a.ClientType',$client_type)
            ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
            ->where(function($query){
                $query->where(function($query){
                    $query ->where('b.payment_provider_id','>',0)
                        ->where('b.payment_type','<>',VipBusinessman::SIGN);
                })->orWhere('b.payment_provider_id',array_values(PaymentOrder::CHANNEL));
            })
            ->first();
        $info['recharge_data']['third_pay'] = $third_pay['amount'] ?? '0.00';
        $info['recharge_data']['third_total'] = $third_pay['people_total'] ?? 0;
        //vip商人
        $vip_pay =  AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as people_total')
            )
            ->where('a.IsAndroid',0)
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->where('b.payment_type',VipBusinessman::SIGN)
            ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();
        $info['recharge_data']['vip_pay'] = $vip_pay['amount'] ?? '0.00';
        $info['recharge_data']['vip_total'] = $vip_pay['people_total'] ?? 0;
        //补充订单
        $supplement_pay = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as people_total')
            )
            ->where('a.IsAndroid',0)
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->where('b.payment_type',PaymentOrder::COMPENSATE)
            ->where('b.payment_provider_id',PaymentOrder::COMPENSATE_KEY)
            ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();
        $info['recharge_data']['supplement_pay'] = $supplement_pay['amount'] ?? '0.00';
        $info['recharge_data']['supplement_total'] = $supplement_pay['people_total'] ?? 0;
        //手动上分
        $basic_system_give = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(RecordTreasureSerial::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                \DB::raw('SUM(b.ChangeScore) as ChangeScore'),
                \DB::raw('COUNT(DISTINCT b.UserID) as people_total')
            )//操作变化金币
            ->where('a.IsAndroid',0)
//            ->where('b.ChangeScore', '>', 0)
            ->where('b.TypeID', RecordTreasureSerial::SYSTEM_GIVE_TYPE)
            ->andFilterBetweenWhere('b.CollectDate',$start_date,$end_date)
            ->andFilterWhere('a.ClientType',$client_type);
//            ->first();
        $system_give = (clone $basic_system_give)->where('b.ChangeScore', '>', 0)->first();
        $system_down = (clone $basic_system_give)->where('b.ChangeScore', '<', 0)->first();
        $info['recharge_data']['system_give'] = realCoins($system_give['ChangeScore'] ?? '0.00');
        $info['recharge_data']['system_give_total'] = $system_give['people_total'] ?? 0;
        $info['recharge_data']['system_down'] = realCoins($system_down['ChangeScore'] ?? '0.00');
        $info['recharge_data']['system_down_total'] = $system_down['people_total'] ?? 0;
        $data_withdraw = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(WithdrawalOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.money) as withdraw_total'),
                \DB::raw('COUNT(DISTINCT b.user_id) as people_total'),
                \DB::raw('COUNT(b.id) as num_total'))
            ->where('a.IsAndroid',0)
            ->where('b.status', WithdrawalOrder::PAY_SUCCESS)
            ->whereNull('b.withdrawal_type')
            ->andFilterBetweenWhere('b.complete_time', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();
        $info['withdrawal_data']['withdraw_amount'] = $data_withdraw['withdraw_total'] ?? '0.00';  //用户
        $info['withdrawal_data']['withdraw_people_total'] = $data_withdraw['people_total'] ?? 0; //用户人数
        //渠道
        $channel_withdraw = ChannelWithdrawRecord::select(
                \DB::raw('SUM(value) as channel_total'),
                \DB::raw('COUNT(DISTINCT channel_id) as people_total'),
                \DB::raw('COUNT(id) as num_total')
            )
            ->where('status',ChannelWithdrawRecord::PAY_SUCCESS)
            ->andFilterBetweenWhere('updated_at', $start_date, $end_date)
            ->first();
        if($client_type){
            $info['withdrawal_data']['channel_amount'] = 0;
            $info['withdrawal_data']['channel_people_total'] = 0;
        }else{
            $info['withdrawal_data']['channel_amount'] = $channel_withdraw['channel_total'] ?? '0.00';
            $info['withdrawal_data']['channel_people_total'] = $channel_withdraw['people_total'] ?? 0;
        }
        //代理
//        $agent_withdraw = AccountsInfo::from(AccountsInfo::tableName().' as a')
//            ->leftJoin(AgentWithdrawRecord::tableName().' as b','a.UserID','=','b.user_id')
//            ->select(
//                \DB::raw('SUM(b.score) as agent_total'),
//                \DB::raw('COUNT(DISTINCT b.user_id) as people_total'),
//                \DB::raw('COUNT(b.id) as num_total')
//            )
//            ->where('a.IsAndroid',0)
//            ->where('b.status',AgentWithdrawRecord::PAY_SUCCESS)
//            ->andFilterBetweenWhere('b.updated_at', $start_date, $end_date)
//            ->andFilterWhere('a.ClientType',$client_type)
//            ->first();
//        $info['withdrawal_data']['agent_amount'] = realCoins($agent_withdraw['agent_total'] ?? '0.00');
//        $info['withdrawal_data']['agent_people_total'] = $agent_withdraw['people_total'] ?? 0;

        //拒绝提现
        $withdrawal_refuse = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(RecordTreasureSerial::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                \DB::raw('SUM(b.ChangeScore) as ChangeScore'),
                \DB::raw('COUNT(DISTINCT b.UserID) as people_total')
            )
            ->where('a.IsAndroid', 0)
            ->where('b.ChangeScore', '<', 0)
            ->where('b.TypeID', RecordTreasureSerial::WITHDRAWAL_REFUSE)
            ->andFilterBetweenWhere('b.CollectDate',$start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();

        $info['withdrawal_data']['withdrawal_refuse'] = realCoins($withdrawal_refuse['ChangeScore']) ?? '0.00';
        $info['withdrawal_data']['withdrawal_refuse_total'] = $withdrawal_refuse['people_total'] ?? 0;

        //支出总额 = 用户+渠道+代理,总人数=用户人数+渠道人数+代理人数
        if($client_type){
            $info['withdrawal_data']['sum_withdraw_amount'] = ($data_withdraw['withdraw_total'] ?? '0.00');
            $info['withdrawal_data']['sum_people_total'] = ($data_withdraw['people_total'] ?? 0);
            $info['withdrawal_data']['sum_num_total'] = ($data_withdraw['num_total'] ?? 0); //总笔数
        }else {
            $info['withdrawal_data']['sum_withdraw_amount'] = ($data_withdraw['withdraw_total'] + $channel_withdraw['channel_total']  ?? '0.00');
            $info['withdrawal_data']['sum_people_total'] = ($data_withdraw['people_total'] + $channel_withdraw['people_total']  ?? 0);
            $info['withdrawal_data']['sum_num_total'] = ($data_withdraw['num_total'] + $channel_withdraw['num_total'] ?? 0); //总笔数
        }
        //玩家账户余额 = 携带金币+银行金币,人数
        $user_score = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(GameScoreInfo::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                \DB::raw('SUM(b.Score) as Score'),
                \DB::raw('SUM(b.InsureScore) as InsureScore'),
                \DB::raw('COUNT(a.UserID) as people_total')
            )
            ->where('a.IsAndroid',0)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();
        $info['other']['user_score'] = realCoins($user_score['Score']+$user_score['InsureScore'] ?? '0.00');
        $info['other']['user_total'] = $user_score['people_total'] ?? 0;
        //活动礼金总额
        $info['cash_gift'] = RecordTreasureSerial::getCashGifts($start_date,$end_date,$client_type);
        /*盈亏和盈亏率*/
        //盈亏=投注-中奖-活动返利（首充赠送、活动赠送）+拒绝，盈亏率 = 筛选时间盈亏/筛选时间投注*100%
        //改为：净盈利=投注-中奖-活动彩金,盈亏率=筛选时间平台盈利/筛选时间投注*100%
        $betting_score = realCoins($prize_moneys['JettonScore']); //筛选时间投注
        $info['profit_loss']['sum_profit_loss']=realCoins($prize_moneys['platform_profit']-$info['cash_gift']['sum_give_cg_coin']) ?? 0;
        if($betting_score > 0 && $info['prize_score']['platform_profit']!=0)
        {
            $info['profit_loss']['sum_profit_loss_rate']=($info['prize_score']['platform_profit']/$betting_score)*100 ?? 0;
        }else{
            $info['profit_loss']['sum_profit_loss_rate']= 0;
        }
        //注册人数,首充,在线人数,绑定人数,绑定率
        //注册人数
        $register_num = AccountsInfo::select('UserID')
            ->select(
                \DB::raw('COUNT(UserID) as total')
            )
            ->where('IsAndroid', 0)
            ->andFilterBetweenWhere('RegisterDate', $start_date, $end_date)
            ->andFilterWhere('ClientType',$client_type)
            ->first();
        $info['people_num']['register_total'] = $register_num['total'] ?? 0;
        //首充
        $first_recharge = PaymentOrder::from(PaymentOrder::tableName().' as a')
            ->select(
                \DB::raw('SUM(a.amount) as amount'),
                \DB::raw('COUNT(a.user_id) as people_total')
            )
            ->rightJoin(\DB::raw('(SELECT MIN (id) AS id FROM '.PaymentOrder::tableName().' WHERE payment_status = '."'".PaymentOrder::SUCCESS."'".' GROUP BY user_id) AS b'),'a.id','=','b.id')
            ->leftJoin(AccountsInfo::tableName().' as c','a.user_id','=','c.UserID')
            ->andFilterBetweenWhere('a.created_at', $start_date, $end_date)
            ->andFilterWhere('c.ClientType',$client_type)
            ->first();
        $info['people_num']['first_recharge_score'] =$first_recharge['amount'] ?? '0.00';
        $info['people_num']['first_recharge_total'] =$first_recharge['people_total'] ?? 0;
        //在线人数和最高在线人数
        $highest_online_sum = StatisticsOnlineData::select('total')->andFilterWhere('client_type',$client_type)
            ->andFilterBetweenWhere('created_at',$start_date, $end_date)->max('total');
        //在线人数改为日活人数
        // ClientType>0 ,1、android,2、ios,3、h5
        $info['people_num']['online_total']= RecordUserLogon::from(RecordUserLogon::tableName().' as a')
            ->leftJoin(AccountsInfo::tableName().' as b','a.UserID','=','b.UserID')
            ->andFilterBetweenWhere('a.CreateDate', $start_date, $end_date)
            ->where(function ($query) use ($client_type){
                if($client_type > 0) {
                    $query->where('b.ClientType',$client_type);
                } else {
                    $query->where('b.ClientType','>',0);
                }
            })
            ->count(\DB::raw("distinct(a.UserID)"));
        $info['people_num']['highest_online_total'] = $highest_online_sum ?? 0;
        //绑定人数
        $bind_num = AccountsInfo::select(
                \DB::raw('COUNT(DISTINCT UserID) as total'))
            ->where('IsAndroid',0)
            ->where('RegisterMobile','<>','')
            ->andFilterBetweenWhere('RegisterDate', $start_date, $end_date)
            ->andFilterWhere('ClientType',$client_type)
            ->first();
        $info['people_num']['bind_total']=$bind_num['total'] ?? 0;
        //绑定率 = 筛选时间注册绑定手机人数/筛选时间注册人数*100%
        if($register_num['total']>0 && $bind_num['total']>0){
            $info['people_num']['bind_rate'] = ($bind_num['total']/$register_num['total'])*100 ?? 0;
        }else{
            $info['people_num']['bind_rate'] = 0;
        }
        /*新增渠道数,代理总数,新增代理数*/
        //新增渠道总数
        if($client_type){
            $info['channel_agent']['new_add_channel_total'] = '/';
        }else{
            $info['channel_agent']['new_add_channel_total'] = ChannelInfo::andFilterBetweenWhere('created_at', $start_date, $end_date)->count();
        }
        //代理总数
        $agent_num =  AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.parent_user_id')
            ->select(
                \DB::raw('COUNT(DISTINCT b.parent_user_id) as people_total'))
            ->where('a.IsAndroid',0)
            ->where('b.parent_user_id','>',0)
            ->andFilterWhere('a.ClientType',$client_type)
            ->andFilterBetweenWhere('b.created_at',$start_date, $end_date)
            ->first();
        $time = [request('start_date', '1970-01-01'), request('end_date',  now()->format('Y-m-d'))];
        $agent = AgentRelation::from(AgentRelation::tableName().' as a')
            ->select(
                \DB::raw("(select sum(reward_score) from ".AgentIncome::tableName()." where user_id=a.user_id and start_date between '".$time[0] ."' and '".$time[1]."' ) as reward_score "),
                \DB::raw("ISNULL((select sum(ChangeScore) from ".RecordTreasureSerial::tableName()." where UserID=a.user_id and TypeID=50 and CollectDate between '".$time[0]." 00:00:00' and '".$time[1]." 23:59:59'), 0) as agent_score")
            )
//            ->andFilterBetweenWhere('a.created_at',$start_date, $end_date)
            ->leftJoin(AccountsInfo::tableName().' as b','b.UserID','=','a.user_id')
            ->get();

        $info['channel_agent']['agent_total'] = $agent_num['people_total'] ?? 0;
        $info['channel_agent']['agent_score'] = realCoins($agent->sum('reward_score') ?? 0);
        $info['channel_agent']['agent_score_withdraw'] = realCoins($agent->sum('agent_score') ?? 0);
        //新增代理数
        $new_add_agent_sum = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.parent_user_id')
            ->select(
                \DB::raw('COUNT(DISTINCT b.parent_user_id) as people_total'))
            ->where('a.IsAndroid',0)
            ->where('b.parent_user_id','>',0)
            ->andFilterBetweenWhere('b.created_at', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->first();
        $info['channel_agent']['new_add_agent_total'] = $new_add_agent_sum['people_total'] ?? 0;
        //游戏数据
        $res =  AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(RecordGameScore::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                \DB::raw('SUM(b.SystemServiceScore) as SystemServiceScore'),
                \DB::raw('SUM(b.StreamScore) as StreamScore'))
            ->where('a.IsAndroid',0)
            ->when($client_type, function ($query) use ($client_type) {
                $query->where('a.ClientType', $client_type);
            })
            ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                $start_date = Carbon::parse($start_date)->format('Y-m-d 00:00:00');
                $end_date = Carbon::parse($end_date)->format('Y-m-d 23:59:59');
                $query->whereBetween('b.UpdateTime', [$start_date, $end_date]);
            })
            ->first();
        //游戏税费
        $info['game_data']['sum_systemServiceScore']=realCoins($res['SystemServiceScore']);
        //游戏流水
        $info['game_data']['sum_streamScore']=realCoins($res['StreamScore']);
        /*注册付费率,ARPU,ARPPU*/
        //注册付费率 = 筛选时间内注册付费人数/筛选时间注册人数*100%
        if($data_recharge['amount']>0 && $register_num['total']>0){
            $info['other']['register_fee_rate'] = ($data_recharge['people_total']/$register_num['total'])*100 ?? 0 ;
        }else{
            $info['other']['register_fee_rate'] = 0;
        }
        //ARPU = 筛选时间内充值 / 筛选时间内注册人数
        //ARPPU = 筛选时间内充值 / 筛选时间内付费用户(充值人数)
        if($data_recharge['amount'] >0 && $register_num['total']>0 && $data_recharge['people_total']>0 ){
            $info['other']['ARPU'] = $data_recharge['amount']/$register_num['total'] ?? 0;
            $info['other']['ARPPU'] = $data_recharge['amount']/$data_recharge['people_total'] ?? 0;
        }else{
            $info['other']['ARPU'] = 0;
            $info['other']['ARPPU'] = 0;
        }
         return ResponeSuccess('请求成功',$info);
    }
    /**
     * 充值详情（内外部充值的二级页面）
     *
     */
    public function payReportDetails()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'client_type'=> ['nullable','in:1,2,3'] //注册来源：1、android，2、ios，3、h5
        ], [
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'client_type.in'  => '注册来源不在可选范围内'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $client_type = request('client_type');
        $arr_type = array_keys(PaymentOrder::OFFICIAL);
        if (request('type') == 1){
            //统计内部充值详情
            $info = AccountsInfo::from(AccountsInfo::tableName().' as a')
                ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
                ->select('b.payment_type',
                    \DB::raw('sum(b.amount) as amount')
                )
                ->where('a.IsAndroid',0)
                ->where('b.payment_status',PaymentOrder::SUCCESS)
                ->andFilterWhere('a.ClientType',$client_type)
                ->whereIn('b.payment_type',$arr_type)
                ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
                ->groupBy('b.payment_type')
                ->pluck('b.amount','b.payment_type');
            $data['official_alipay']    = $info['official_alipay'] ?? 0;//官方支付宝
            $data['official_union']     = $info['official_union'] ?? 0;//官方银联
            $data['official_wechat']    = $info['official_wechat'] ?? 0;//官方微信
            array_walk_recursive($data,'decimal_walk');
            return ResponeSuccess('请求成功',$data);
        }elseif (request('type') == 2){
            array_push($arr_type,VipBusinessman::SIGN); //vip商人
            array_push($arr_type,PaymentOrder::COMPENSATE); //补单
            //统计外部充值详情
            $data =AccountsInfo::from(AccountsInfo::tableName().' as a')
                ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
                ->leftJoin(PaymentProvider::tableName().' as c','b.payment_provider_id','=','c.id')
                ->leftJoin(PaymentConfig::tableName().' as d','c.payment_config_id','=','d.id')
                ->select('d.id',\DB::raw('sum(b.amount) as amount'))
                ->where('a.IsAndroid',0)
                ->where('b.payment_status',PaymentOrder::SUCCESS)
                ->andFilterWhere('a.ClientType',$client_type)
                ->whereNotIn('b.payment_type',$arr_type)
                ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
                ->groupBy('d.id')
                ->pluck('amount','id');
            //查询四方平台
            $platform = PaymentConfig::pluck('name','id');
            $list = [];
            foreach ($platform as $k => $v){
                $list[] = ['name'=>$v,'amount'=>$data[$k] ?? 0] ;
            }
            return ResponeSuccess('请求成功',$list);
        }else{
            return ResponeFails('充值类型有误');
        }
    }

    /**
     * 系统报表-平台盈利详情
     *
     */
    public function platformProfitDetails()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'client_type'=> ['nullable','in:1,2,3'] //注册来源：1、android，2、ios，3、h5
        ], [
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'client_type.in'  => '注册来源不在可选范围内'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $client_type = request('client_type');

       try{
            /*中奖*/
        $info = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(RecordGameScore::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                'b.PlatformID as id',
                \DB::raw('SUM(b.JettonScore-b.RewardScore) as platform_profit')) //平台盈利=投注-中奖
            ->where('a.IsAndroid',0)
            ->andFilterBetweenWhere('b.UpdateTime', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->groupBy('b.PlatformID')
            ->get()->toArray();
        $list = $info ? array_column($info, null, 'id') : [];
        // 平台信息
        $platform = OuterPlatform::select('id','name')->get()->toArray();
        $i = 0;
        foreach ($platform as $key => &$value) {
            $value['number'] = ++$i;
            if(array_key_exists($value['id'], $list)){
                $value['platform_profit'] = realCoins($list[$value['id']]['platform_profit'] ?? '0.00'); //平台盈利
            } else {
                $value['platform_profit'] = 0; //平台盈利
            }
            unset($value['id']);
        }
        return ResponeSuccess('请求成功',$platform);
       }catch (\Exception $exception){echo $exception;
           return ResponeFails('异常错误');
       }

    }
    /**
     * 系统报表-有效投注详情
     *
     */
    public function jettonScoreDetails()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'client_type'=> ['nullable','in:1,2,3'] //注册来源：1、android，2、ios，3、h5
        ], [
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'client_type.in'  => '注册来源不在可选范围内'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $client_type = request('client_type');

       try{
            /*中奖*/
        $info = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(RecordGameScore::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                'b.PlatformID as id',
                \DB::raw('SUM(b.JettonScore) as JettonScore'),   //投注
                \DB::raw('COUNT(DISTINCT b.UserID) as people_total')) //投注人数
            ->where('a.IsAndroid',0)
            ->andFilterBetweenWhere('b.UpdateTime', $start_date, $end_date)
            ->andFilterWhere('a.ClientType',$client_type)
            ->groupBy('b.PlatformID')
            ->get()->toArray();
        $list = $info ? array_column($info, null, 'id') : [];
        // 平台信息
        $platform = OuterPlatform::select('id','name')->get()->toArray();
        $i = 0;
        foreach ($platform as $key => &$value) {
            $value['number'] = ++$i;
            if(array_key_exists($value['id'], $list)){
                $value['jetton_score'] = realCoins($list[$value['id']]['JettonScore'] ?? '0.00'); //投注
                $value['people_total'] = $list[$value['id']]['people_total'] ?? 0; //投注人数
            } else {
                $value['jetton_score'] = 0; //投注
                $value['people_total'] = 0;
            }
            unset($value['id']);
        }
        return ResponeSuccess('请求成功',$platform);
       }catch (\Exception $exception){echo $exception;
           return ResponeFails('异常错误');
       }

    }

    /**
     * 系统报表-中奖详情
     *
     */
    public function prizeMoneysDetails()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'client_type'=> ['nullable','in:1,2,3'] //注册来源：1、android，2、ios，3、h5
        ], [
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'client_type.in'  => '注册来源不在可选范围内'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $client_type = request('client_type');
        try{
            /*中奖*/
            $info = AccountsInfo::from(AccountsInfo::tableName().' as a')
                ->leftJoin(RecordGameScore::tableName().' as b','a.UserID','=','b.UserID')
                ->select(
                    'b.PlatformID as id',
                    \DB::raw('SUM(b.RewardScore) as reward_score')) //中奖
                ->where('a.IsAndroid',0)
                ->andFilterBetweenWhere('b.UpdateTime', $start_date, $end_date)
                ->andFilterWhere('a.ClientType',$client_type)
                ->groupBy('b.PlatformID')
                ->get()->toArray();
            $list = $info ? array_column($info, null, 'id') : [];
            // 平台信息
            $platform = OuterPlatform::select('id','name')->get()->toArray();
            $i = 0;
            foreach ($platform as $key => &$value) {
                $value['number'] = ++$i;
                if(array_key_exists($value['id'], $list)){
                    $value['reward_score'] = realCoins($list[$value['id']]['reward_score'] ?? '0.00'); //中奖
                } else {
                    $value['reward_score'] = 0; //中奖
                }
                unset($value['id']);
            }
            return ResponeSuccess('请求成功',$platform);
        }catch (\Exception $exception){echo $exception;
            return ResponeFails('异常错误');
        }

    }

    /**
     * 系统报表-流水详情
     *
     */
    public function streamScoreDetails()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'client_type'=> ['nullable','in:1,2,3'] //注册来源：1、android，2、ios，3、h5
        ], [
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'client_type.in'  => '注册来源不在可选范围内'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $client_type = request('client_type');
        try{
            /*游戏数据*/
            $info = AccountsInfo::from(AccountsInfo::tableName().' as a')
                ->leftJoin(RecordGameScore::tableName().' as b','a.UserID','=','b.UserID')
                ->select(
                    'b.PlatformID as id',
                    \DB::raw('SUM(b.StreamScore) as stream_score'))//游戏流水
                ->where('a.IsAndroid',0)
                ->andFilterBetweenWhere('b.UpdateTime', $start_date, $end_date)
                ->andFilterWhere('a.ClientType',$client_type)
                ->groupBy('b.PlatformID')
                ->get()->toArray();
            $list = $info ? array_column($info, null, 'id') : [];
            // 平台信息
            $platform = OuterPlatform::select('id','name')->get()->toArray();
            $i = 0;
            foreach ($platform as $key => &$value) {
                $value['number'] = ++$i;
                if(array_key_exists($value['id'], $list)){
                    $value['stream_score'] = realCoins($list[$value['id']]['stream_score'] ?? '0.00'); //平台盈利
                } else {
                    $value['stream_score'] = 0; //平台盈利
                }
                unset($value['id']);
            }
            return ResponeSuccess('请求成功',$platform);
        }catch (\Exception $exception){echo $exception;
            return ResponeFails('异常错误');
        }

    }

    public function accountBalanceList()
    {
        $list = StatisticsBalance::query()->orderBy('statistical_date', 'desc')->paginate(12);
        return $this->response->paginator($list, new AccountBalanceListTransformer());
    }
}
