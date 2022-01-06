<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentConfig;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\PaymentProvider;
use Models\AdminPlatform\StatisticsGameData;
use Models\AdminPlatform\VipBusinessman;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentIncome;
use Models\Agent\AgentInfo;
use Models\Agent\AgentRelation;
use Models\Agent\AgentWithdrawRecord;
use Models\Agent\ChannelIncome;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Models\Agent\ChannelWithdrawRecord;
use Models\Platform\OnLineStreamInfo;
use Models\Record\RecordTreasureSerial;
use Validator;

class ReportFormsController extends Controller
{
    //报表信息
    public function getInfo()
    {
        Validator::make(request()->all(), [
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'start_time.date' => '无效日期',
            'end_time.date'   => '无效日期',
        ])->validate();
        $today=date('Y-m-d',time());
        $startTime = request('startTime') ?? $today;
        $endTime = request('endTime');
        $unit = request('unit');
        $timeData = timeTransform([$startTime,$endTime],$unit);
        if(!$timeData){
            return ResponeFails('时间格式有误');
        }
        //上月日期
        if($unit == 'month'){
            $lastStartTime = date("Y-m-d",strtotime("last month",strtotime($startTime)));
            $lastTimeData  = timeTransform([$lastStartTime,$endTime],$unit);
        }
        $info = [
            'income'            => [],  //收入
            'last_income'       => [],  //上月收入
            'expense'           => [],  //支出
            'last_expense'      => [],  //上月支出
            'other'             => [],  //其他
            'game_data'         => [],  //游戏数据
            'profit_loss'       => [],  //盈亏和盈亏率
            'last_profit_loss'  => [],  //上月盈亏和盈亏率
        ];
        /*收入*/
        //内部充值
        $inner_keys=array_keys(PaymentOrder::OFFICIAL_KEYS);
        array_push($inner_keys,VipBusinessman::SIGN);
        $info['income']['official_pay'] = PaymentOrder::select('amount')->where('payment_status',PaymentOrder::SUCCESS)->whereBetween('success_time',$timeData)
            ->whereIn('payment_type',$inner_keys)->sum('amount');
        //外部充值
        $info['income']['third_pay'] = PaymentOrder::select('amount')->where('payment_status',PaymentOrder::SUCCESS)->whereBetween('success_time',$timeData)
            ->where(function($query){
                $query->where(function($query){
                    $query ->where('payment_provider_id','>',0)
                        ->where('payment_type','<>',VipBusinessman::SIGN);
                })->orWhere('payment_provider_id',array_values(PaymentOrder::CHANNEL));
            })
            ->sum('amount');
        //渠道充值
        $info['income']['channel_pay'] = ChannelUserRelation::from('channel_user_relation as a')
            ->leftJoin('admin_platform.dbo.payment_orders as b','a.user_id','=','b.user_id')
            ->whereBetween('b.success_time',$timeData)
            ->where('b.payment_status',PaymentOrder::SUCCESS)->sum('amount');
        //总充值(内部充值+外部充值)
        $info['income']['pay_total'] = $info['income']['official_pay']+$info['income']['third_pay'];//array_sum($info['income']);
        /*上月收入*/
        if($unit == 'month') {
            //内部充值
            $inner_keys=array_keys(PaymentOrder::OFFICIAL_KEYS);
            array_push($inner_keys,VipBusinessman::SIGN);
            $info['last_income']['official_pay'] = PaymentOrder::select('amount')->where('payment_status',PaymentOrder::SUCCESS)->whereBetween('success_time',$lastTimeData)
                ->whereIn('payment_type',$inner_keys)->sum('amount');
            //外部充值
            $info['last_income']['third_pay'] = PaymentOrder::select('amount')->where('payment_status',PaymentOrder::SUCCESS)->whereBetween('success_time',$lastTimeData)
                ->where(function($query){
                    $query->where(function($query){
                        $query ->where('payment_provider_id','>',0)
                            ->where('payment_type','<>',VipBusinessman::SIGN);
                    })->orWhere('payment_provider_id',array_values(PaymentOrder::CHANNEL));
                })
                ->sum('amount');
            //渠道充值
            $info['last_income']['channel_pay'] = ChannelUserRelation::from('channel_user_relation as a')
                ->leftJoin('admin_platform.dbo.payment_orders as b','a.user_id','=','b.user_id')
                ->whereBetween('b.success_time',$lastTimeData)
                ->where('b.payment_status',PaymentOrder::SUCCESS)->sum('amount');
            //总充值
            $info['last_income']['pay_total'] = $info['last_income']['official_pay']+$info['last_income']['third_pay'];
        }
        /*支出*/
        //vip商人
        $info['expense']['vip_withdraw_total']=WithdrawalOrder::where('status', WithdrawalOrder::PAY_SUCCESS)
            ->where('withdrawal_type',WithdrawalOrder::WITHDRAWAL_TYPE)->whereBetween('created_at',$timeData)->sum('real_gold_coins');
        //总兑换额
        $info['expense']['withdraw_total']= WithdrawalOrder::where('status', WithdrawalOrder::PAY_SUCCESS)
            ->whereNull('withdrawal_type')->whereBetween('created_at',$timeData)->sum('real_gold_coins');
        //渠道佣金总额
        $channel_withdraw = ChannelWithdrawRecord::select('value')->where('status',ChannelWithdrawRecord::PAY_SUCCESS)->whereBetween('updated_at',$timeData)->sum('value');
        $info['expense']['channel_total'] = moneyToCoins($channel_withdraw);
        //代理佣金
        $info['expense']['agent_total'] = AgentWithdrawRecord::select('score')->where('status',AgentWithdrawRecord::PAY_SUCCESS)->whereBetween('updated_at',$timeData)->sum('score');
        //各种活动礼金
        $cg_info = RecordTreasureSerial::getCashGifts($timeData);
        $info['expense'] = array_merge($info['expense'],$cg_info);
        //活动礼金总额，去除后台赠送的
        $info['expense']['cg_total'] = array_sum($cg_info) - $cg_info['give_cg'];
        //总支出（总支出 = (vip商人额+兑换额) + 佣金(代理佣金总额+渠道佣金总额)）
        $info['expense']['expense_total']=$info['expense']['vip_withdraw_total']+ $info['expense']['withdraw_total']
            +$info['expense']['agent_total']+$info['expense']['channel_total'];
        array_walk_recursive($info['expense'], 'coin_walk');
        /*上月支出*/
        if($unit == 'month') {
            //vip商人支出
            $info['last_expense']['vip_withdraw_total'] = WithdrawalOrder::where('status', WithdrawalOrder::PAY_SUCCESS)
                ->where('withdrawal_type',WithdrawalOrder::WITHDRAWAL_TYPE)->whereBetween('created_at',$lastTimeData)->sum('real_gold_coins');
            //总兑换额
            $info['last_expense']['withdraw_total'] = WithdrawalOrder::where('status', WithdrawalOrder::PAY_SUCCESS)
                ->whereNull('withdrawal_type')->whereBetween('created_at', $lastTimeData)->sum('real_gold_coins');
            //渠道佣金总额
            $last_channel_withdraw = ChannelWithdrawRecord::select('value')->where('status',ChannelWithdrawRecord::PAY_SUCCESS)->whereBetween('updated_at',$lastTimeData)->sum('value');
            $info['last_expense']['channel_total'] = moneyToCoins($last_channel_withdraw);
            //代理佣金总额
            $info['last_expense']['agent_total'] = AgentWithdrawRecord::select('score')->where('status',AgentWithdrawRecord::PAY_SUCCESS)->whereBetween('updated_at',$lastTimeData)->sum('score');
            //上月总支出（总支出 = vip商人+兑换额) + 佣金(代理佣金总额+渠道佣金总额)）
            $info['last_expense']['expense_total']=$info['last_expense']['vip_withdraw_total']+ $info['last_expense']['withdraw_total']+
                $info['last_expense']['agent_total']+$info['last_expense']['channel_total'];
            array_walk_recursive($info['last_expense'], 'coin_walk');
        }
        /*其他*/
        //注册人数
        $info['other']['register_sum'] = AccountsInfo::select('UserID')->where('IsAndroid', 0)->whereBetween('RegisterDate',$timeData)->count();
        //在线人数
        $info['other']['online_sum'] = OnLineStreamInfo::select('OnLineCountSum')->whereBetween('InsertDateTime',$timeData)->max('OnLineCountSum') ?? 0;
        //绑定人数
        $info['other']['bind_sum'] = AccountsInfo::select('UserID')->where('RegisterMobile','<>','')->whereBetween('RegisterDate',$timeData)->count();
        $pay_sum = PaymentOrder::select(\DB::raw('COUNT(user_id) as pay_sum'),'user_id')->where('payment_status',PaymentOrder::SUCCESS)
            ->whereBetween('success_time',$timeData)->groupBy('user_id')->pluck('pay_sum','user_id')->toArray();
        //充值人数
        $info['other']['pay_user_sum'] = count($pay_sum);
        //充值笔数
        $info['other']['pay_sum'] = array_sum($pay_sum);
        //渠道总数
        $info['other']['channel_sum'] =ChannelInfo::count();
        //代理总数
        $info['other']['agent_sum'] = AgentRelation::where('parent_user_id','>',0)->distinct('parent_user_id')->count('parent_user_id');
        //新增渠道总数
        $info['other']['new_add_channel_sum'] = ChannelInfo::whereBetween('created_at',$timeData)->count();
        //新增代理总数
        $info['other']['new_add_agent_sum'] = AgentRelation::where('parent_user_id','>',0)->whereBetween('created_at',$timeData)->distinct('parent_user_id')->count('parent_user_id');
        //游戏数据
        $res = StatisticsGameData::select(
            \DB::raw('sum(sum_changeScore) as sum_changeScore'),//玩家总输赢
            \DB::raw('sum(sum_jettonScore) as sum_jettonScore'),//总下注量
            \DB::raw('sum(sum_systemServiceScore) as sum_systemServiceScore'),//总税费
            \DB::raw('sum(sum_streamScore) as sum_streamScore')//总流水
        )->whereBetween('statistics_time',$timeData)->get()->toArray();
        //游戏输赢
        $info['game_data']['sum_changeScore']= -(realCoins($res[0]['sum_changeScore']));
        //游戏有效投注
        $info['game_data']['sum_jettonScore']=realCoins($res[0]['sum_jettonScore']);
        //游戏税费
        $info['game_data']['sum_systemServiceScore']=realCoins($res[0]['sum_systemServiceScore']);
        //游戏流水
        $info['game_data']['sum_streamScore']=realCoins($res[0]['sum_streamScore']);
        /*盈亏和盈亏率*/
        //盈亏=总充值-总支出-渠道佣金-代理佣金  = 总充值-总支出
        $info['profit_loss']['sum_profit_loss']=$info['income']['pay_total']-$info['expense']['expense_total'];
        //盈亏率=（总充值-总支出-渠道佣金-代理佣金）/（总支出+渠道佣金+代理佣金）= 盈亏/总支出
        if($info['expense']['expense_total'] ==0 || $info['profit_loss']['sum_profit_loss']==0)
        {
            $info['profit_loss']['sum_profit_loss_rate']= 0;
        }else{
            $info['profit_loss']['sum_profit_loss_rate']=$info['profit_loss']['sum_profit_loss']/$info['expense']['expense_total'];
        }
        /*上月盈亏和盈亏率*/
        if($unit == 'month') {
            //上月盈亏=上月总充值-上月总支出-上月渠道佣金-上月代理佣金  = 上月总充值-上月总支出
            $info['last_profit_loss']['sum_profit_loss'] = $info['last_income']['pay_total'] - $info['last_expense']['expense_total'];
            //上月盈亏率=（上月总充值-上月总支出-上月渠道佣金-上月代理佣金）/（上月总支出+上月渠道佣金+上月代理佣金）= 上月盈亏/上月总支出
            if ($info['last_expense']['expense_total'] == 0 || $info['last_profit_loss']['sum_profit_loss'] == 0) {
                $info['last_profit_loss']['sum_profit_loss_rate'] = 0;
            } else {
                $info['last_profit_loss']['sum_profit_loss_rate'] = $info['last_profit_loss']['sum_profit_loss']/$info['last_expense']['expense_total'];
            }
        }
        //保留2位小数
        array_walk_recursive($info,'decimal_walk');
        return ResponeSuccess('请求成功',$info);
    }

    /**
     * 充值详情（内外部充值的二级页面）
     *
     */
    public function payReportDetails()
    {
        Validator::make(request()->all(), [
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'start_time.date' => '无效日期',
            'end_time.date'   => '无效日期',
        ])->validate();
        $startTime = request('startTime') ?? date('Y-m-d',time());
        $endTime = request('endTime');
        $arr_type = array_keys(PaymentOrder::OFFICIAL);
        $arr_type[] = 'vip_business';//加入vip
        if (request('type') == 1){
            //统计内部充值详情
            $info = PaymentOrder::select('payment_type',\DB::raw('sum(amount) as amount'))
                ->where('payment_status',PaymentOrder::SUCCESS)
                ->whereIn('payment_type',$arr_type)
                ->andFilterBetweenWhere('success_time', $startTime, $endTime)
                ->groupBy('payment_type')
                ->pluck('amount','payment_type');
            $data['official_alipay']    = $info['official_alipay'] ?? 0;//官方支付宝
            $data['official_union']     = $info['official_union'] ?? 0;//官方银联
            $data['official_wechat']    = $info['official_wechat'] ?? 0;//官方微信
            $data['vip_business']       = $info['vip_business'] ?? 0;//官方vip商人
            array_walk_recursive($data,'decimal_walk');
            return ResponeSuccess('请求成功',$data);
        }elseif (request('type') == 2){
            //统计外部充值详情
            $data = PaymentOrder::from('payment_orders as a')
                ->select('c.id',\DB::raw('sum(a.amount) as amount'))
                ->leftJoin(PaymentProvider::tableName().' as b','a.payment_provider_id','=','b.id')
                ->leftJoin(PaymentConfig::tableName().' as c','b.payment_config_id','=','c.id')
                ->where('a.payment_status',PaymentOrder::SUCCESS)
                ->whereNotIn('a.payment_type',$arr_type)
                ->andFilterBetweenWhere('success_time', $startTime, $endTime)
                ->groupBy('c.id')
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

}
