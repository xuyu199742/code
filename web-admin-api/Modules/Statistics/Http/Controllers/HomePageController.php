<?php
//首页统计
namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\StatisticsOnlineData;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Platform\OnLineStreamInfo;
use Models\Treasure\RecordScoreDaily;
class HomePageController extends Controller
{
    // 统计单位，图表呈现,1、输赢状况（万）3、有效投注（万）4、在线玩家，最近七天数据
    // 输赢状况（万）
    public function chart_winlose()
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $lists = RecordScoreDaily::select(
            DB::raw("UpdateDate as create_time"),
            DB::raw('sum(JettonScore-RewardScore) as ChangeScore'),  //输赢状况改为：平台盈利=投注-中奖
            DB::raw('sum(SystemServiceScore) as SystemServiceScore')  //税费
        )
            ->andFilterBetweenWhere('UpdateDate', $start_date, $end_date)
            ->groupBy('UpdateDate')
            ->get();
        $r = $lists -> toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $list[$value] = [
                'ChangeScore' => 0,
                'SystemServiceScore' => 0
            ];
            foreach ($r as $item){
                if($item['create_time'] == $value){
                    $list[$value] = [
                        'ChangeScore' => realCoins($item['ChangeScore']),
                        'SystemServiceScore' => realCoins($item['SystemServiceScore'])
                    ];
                }
            }
        }
        return ResponeSuccess('请求成功', $list);
    }

    //充值量（万）
    public function chart_pay()
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $lists = PaymentOrder::select(
            DB::raw("CONVERT(varchar(100), created_at, 23) as create_time"),
            DB::raw("SUM(amount) as sum_amount")
        )
            ->where('payment_status', PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('created_at',$start_date, $end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), created_at, 23)"))
            ->pluck('sum_amount','create_time');
        $r = $lists -> toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $list[$value] = 0;
            if(isset($r[$value])){
                $list[$value] = $r[$value];
            }
        }
        return ResponeSuccess('请求成功', $list);
    }

    //有效投注（万）
    public function chart_bet()
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $lists = RecordScoreDaily::select(
            DB::raw("UpdateDate as create_time"),
            DB::raw('SUM(JettonScore) as JettonScore')  //输赢状况
        )
            ->andFilterBetweenWhere('UpdateDate', $start_date, $end_date)
            ->groupBy('UpdateDate')
            ->pluck('JettonScore','create_time');
        $r = $lists -> toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $list[$value] = 0;
            if(isset($r[$value])){
                $list[$value] = realCoins($r[$value]);
            }
        }
        return ResponeSuccess('请求成功', $list);
    }

    //在线玩家（个）
    public function chart_online()
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $lists = OnLineStreamInfo::select(
            DB::raw("CONVERT(varchar(100), InsertDateTime, 23) as insert_date_time"),
            DB::raw('MAX(OnLineCountSum) as OnLineCountSum')
        )
            ->andFilterBetweenWhere('InsertDateTime',$start_date, $end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), InsertDateTime, 23)"))
            ->pluck('OnLineCountSum','insert_date_time');
        $r = $lists -> toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $list[$value] = 0;
            if(isset($r[$value])){
                $list[$value] = $r[$value];
            }
        }
        return ResponeSuccess('请求成功', $list);
    }
    /*
     * 下注量和输赢(单位) 转换
     * 新增注册，下注信息，充值信息，在线人数
     * 今日，昨日，本周，上周，本月，上月
     */
    public function report_forms()
    {
        //今日 新增注册，下注信息，充值信息，在线人数
        $today = Carbon::now()->toDateString();
        $dates = array(
            '0' => array(
                'start_date' => $today,   // 今日;
                'end_date'   => $today,
            ),
            '1' => array(
                'start_date' => Carbon::yesterday()->toDateString(),     // 昨日
                'end_date' => Carbon::yesterday()->toDateString(),
            ),
            '2' => array(
                'start_date' => Carbon::now()->startOfWeek()->toDateString(),     // 本周的开始时间（本周一）
                'end_date' => $today,                                           // 本周的结束时间（本周的今日日期）
            ),
            '3' => array(
                'start_date' => Carbon::now()->subWeek()->startOfWeek()->toDateString(),   // 上周的开始时间（上周一）
                'end_date' => Carbon::now()->subWeek()->endOfWeek()->toDateString(),     // 上周的结束时间（上周日）
            ),
            '4' => array(
                'start_date' => date('Y-m-01', strtotime(date("Y-m-d"))),       // 本月的开始时间（本月01号）
                'end_date' => $today                                                        // 本月的结束时间（本月的今日日期）
            ),
            '5' => array(
                'start_date' => date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')), // 上月的开始时间（上月01号）
                'end_date' => date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'))    // 上月的结束时间（上月最后一天）
            )
        );
       foreach ($dates as $k => $v)
        {
            //新增注册
            $register = AccountsInfo::select(
                    DB::raw('Count(UserID) as register')
                )
                ->where('IsAndroid', 0)
                ->andFilterBetweenWhere('RegisterDate',$v['start_date'],$v['end_date'])
                ->first();
            $list[$k]['register']['new_register'] = $register['register'] ?? 0;
            //新增总人数
            $register_sum = AccountsInfo::select(
                DB::raw('Count(UserID) as register')
            )
                ->where('IsAndroid', 0)
                ->where('RegisterDate', '<=', $v['end_date'] . ' 23:59:59')
                ->first();
            $list[$k]['register']['register']= $register_sum['register'];
            //下注量和输赢
            $bets = RecordScoreDaily::select(
                    DB::raw('COUNT(DISTINCT UserID) as bet_num'),
                    DB::raw('SUM(JettonScore) as JettonScore'),     //下注量
                    DB::raw('SUM(ChangeScore) as ChangeScore')      //输赢积分
                )
                ->andFilterBetweenWhere('UpdateDate',$v['start_date'],$v['end_date'])
                ->first();
            $list[$k]['bets']['JettonScore']=realCoins($bets['JettonScore']) ?? 0;
            $list[$k]['bets']['ChangeScore']=-(realCoins($bets['ChangeScore'])) ?? 0;
            $list[$k]['bets']['bet_num']= $bets['bet_num'] ?? 0;
            //充值和充值人数
            $pay = PaymentOrder::select(
                DB::raw("SUM(amount) as sum_amount"),
                DB::raw("COUNT(distinct user_id) as total")
            )
                ->where('payment_status', PaymentOrder::SUCCESS)
                ->andFilterBetweenWhere('created_at',$v['start_date'],$v['end_date'])
                ->first();
            $list[$k]['pay']['score'] = $pay['sum_amount'] ?? 0;
            $list[$k]['pay']['total'] = $pay['total'] ?? 0;

            $withdraw = WithdrawalOrder::select(
                    DB::raw("SUM(money) as sum_real_money"),
                    DB::raw("COUNT(distinct user_id) as withdraw_people")
                )
                ->where('status', WithdrawalOrder::PAY_SUCCESS)
                ->andFilterBetweenWhere('created_at',$v['start_date'],$v['end_date'])
                ->first();
            $list[$k]['withdraw']['score'] = $withdraw['sum_real_money'] ?? 0;
            $list[$k]['withdraw']['total'] = $withdraw['withdraw_people'] ?? 0;
            //首充和首充人数
            $sub = PaymentOrder::from(PaymentOrder::tableName() . ' as a')
                ->select(
                    \DB::raw("MIN (a.id) AS id")
                )
                ->where('a.payment_status', PaymentOrder::SUCCESS)
                ->groupBy(\DB::raw("a.user_id"));
            $first_pay = PaymentOrder::from(PaymentOrder::tableName() . ' as c')
                ->select(
                    \DB::raw("COUNT (DISTINCT c.user_id) AS num"),
                    \DB::raw("SUM (c.amount) AS amount")
                )
                ->rightJoinSub($sub, 'b', 'b.id', '=', 'c.id')
                ->andFilterBetweenWhere('c.created_at',$v['start_date'],$v['end_date'])
                ->first();
            $list[$k]['first_recharge']['score']= $first_pay['amount'] ?? 0;
            $list[$k]['first_recharge']['total'] = $first_pay['num'] ?? 0;
            //最高在线人数
            $list[$k]['online']= StatisticsOnlineData::select(
                    DB::raw('MAX(total) as total')
                )
                ->where('client_type',-1)
                ->andFilterBetweenWhere('created_at',$v['start_date'],$v['end_date'])
                ->first();
            //总营收
            $list[$k]['revenue_sum']=$pay['sum_amount']-$withdraw['sum_real_money'] ?? 0;
        }
        return ResponeSuccess('请求成功', $list);
    }
    /*
     * 首页中在线玩家折线图
     * @return Response
     * */
    public function polygonal_chart_online()
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $list=OnLineStreamInfo::select(
            DB::raw('CONVERT(varchar(16),InsertDateTime, 120) as create_at'),
            DB::raw('MAX(OnLineCountSum) as OnLineCountSum')
        )
        ->andFilterBetweenWhere('InsertDateTime',$start_date, $end_date)
        ->groupBy(DB::raw('CONVERT(varchar(16),InsertDateTime, 120)'))
        ->orderBy('create_at','asc')
        ->get();
        return ResponeSuccess('请求成功', $list);
    }
}
