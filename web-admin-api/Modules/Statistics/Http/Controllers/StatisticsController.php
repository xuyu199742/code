<?php

namespace Modules\Statistics\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\StatisticsRechargeChannels;
use Models\AdminPlatform\StatisticsRechargeRates;
use Models\AdminPlatform\StatisticsRetentionChannels;
use Models\AdminPlatform\StatisticsRetentions;
use Models\AdminPlatform\StatisticsWinLose;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Models\Treasure\RecordDrawScore;
use Models\Treasure\RecordScoreDaily;
use Models\Record\RecordUserLogon;
use Models\Platform\GameKindItem;
use Models\Treasure\RecordGameScore;
use Modules\Statistics\Http\Service\StaticsService;
use Validator;


class StatisticsController extends Controller
{
    /**
     * 全局统计-游戏详情
     **/
    // 实时输赢和总下注（单位）转换
    // 实时输赢改为：平台盈利=投注-中奖
    public function global_statistics(Request $request)
    {
        $game_kinds       = GameKindItem::with(['rooms.gameScoreLocker' => function ($query) {
            $query->select(['ServerID', \DB::raw('COUNT(UserID) as total')])->groupBy('ServerID');
        }, 'rooms'])->select(['KindID', 'GameID', 'KindName'])->get();
        $game_kinds_array = $game_kinds->toArray();
        //return $game_kinds_array;
        $game_score       = RecordGameScore::select(['ServerID', 'KindID', 'ServerLevel', \DB::raw('SUM(JettonScore-RewardScore) as SystemScore'), \DB::raw('SUM(JettonScore) as JettonScore'),
            \DB::raw('SUM(StreamScore) as StreamScore'),\DB::raw('SUM(SystemServiceScore) as SystemServiceScore')])
            ->whereDate('UpdateTime', date('Y-m-d'))
            ->groupBy(['ServerID', 'KindID', 'ServerLevel'])
            ->get();
        $game_score_array = collect($game_score->toArray())->keyBy('ServerID');
        //return  $game_score_array;
        $SystemScoreSum = 0;
        $JettonScoreSum = 0;
        $StreamScoreSum = 0;
        $SystemServiceScoreSum = 0;
        $kinds          = [];
        foreach ($game_kinds_array as $key => $game) {
            $game_kinds_array[$key]['SystemScore'] = 0;
            $game_kinds_array[$key]['JettonScore'] = 0;
            $game_kinds_array[$key]['StreamScore'] = 0;
            $game_kinds_array[$key]['SystemServiceScore'] = 0;
            $kinds[]                               = [
                'KindID' => $game['KindID'],
                'Name'   => $game['KindName']
            ];
            foreach ($game['rooms'] as $item => $room) {
                $game_kinds_array[$key]['SystemScore']                 += isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['SystemScore']) : 0;
                $game_kinds_array[$key]['JettonScore']                 += isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['JettonScore']) : 0;
                $game_kinds_array[$key]['StreamScore']                 += isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['StreamScore']) : 0;
                $game_kinds_array[$key]['SystemServiceScore']          += isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['SystemServiceScore']) : 0;
                $game_kinds_array[$key]['rooms'][$item]['SystemScore'] = isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['SystemScore']) : 0;
                $game_kinds_array[$key]['rooms'][$item]['JettonScore'] = isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['JettonScore']) : 0;
                $game_kinds_array[$key]['rooms'][$item]['StreamScore'] = isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['StreamScore']) : 0;
                $game_kinds_array[$key]['rooms'][$item]['SystemServiceScore'] = isset($game_score_array[$room['ServerID']]) ? realCoins($game_score_array[$room['ServerID']]['SystemServiceScore']) : 0;
            }
            $SystemScoreSum += realCoins($game_kinds_array[$key]['SystemScore']);
            $JettonScoreSum += realCoins($game_kinds_array[$key]['JettonScore']);
            $StreamScoreSum += realCoins($game_kinds_array[$key]['StreamScore']);
            $SystemServiceScoreSum += realCoins($game_kinds_array[$key]['SystemServiceScore']);
        }

        $list = array(
            'game_kinds'     => $game_kinds_array,
            'SystemScoreSum' => $SystemScoreSum,
            'JettonScoreSum' => $JettonScoreSum,
            'StreamScoreSum' => $StreamScoreSum,
            'SystemServiceScoreSum' => $SystemServiceScoreSum,
            'kinds'          => $kinds
        );
        return ResponeSuccess('请求成功', $list);
    }
    /**
     * 全局统计—实时输赢
     *
     * @return \Dingo\Api\Http\Response
     */
    // TODO:存储过程要修改
    // 实时输赢（单位）转换
    public function real_time_win_lose()
    {
        $start_time = Carbon::today()->toDateTimeString();
        $end_time   = Carbon::now()->toDateString() . ' 23:59:59.999';
        $list       = StatisticsWinLose::with(['kinditem:KindID,KindName'])
            ->where('create_time', '>=', $start_time)//当天的开始时间
            ->where('create_time', '<=', $end_time)//当天的最后时间
            ->selectRaw('kind_id,SUM(system_score) as system_score_count,create_time')
            //使用原生sql,计算总和，同理还有whereRaw等，只要加了Raw就可以用原生语句
            ->groupBy('kind_id', 'create_time')//根据游戏类型和日期来分组
            ->orderBy('create_time', 'asc')
            ->get();//按时间排序
        $result     = array();
        foreach ($list as $key => $value) {
            $value['system_score_count'] = realCoins($value['system_score_count']);
            $value['create_time']        = strtotime($value['create_time']);
            $result[$value['kind_id']][] = $value;
        }
        return ResponeSuccess('请求成功', $result);
    }

    /**
     * 每日统计-注册统计
     * @return Response
     */
    // 每日统计-注册统计中的按注册渠道统计
    public function register_statistics(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $dates      = getDateRange($start_date, $end_date, 30);
        $list       = array();
        foreach ($dates as $k => $v) {
            $register_num[$k] = AccountsInfo::select(
                'ClientType',
                \DB::raw('COUNT(UserID) as total')
            )
                ->where('RegisterDate', '>=', $v . ' 00:00:00')
                ->where('RegisterDate', '<=', $v . ' 23:59:59')
                ->where('IsAndroid', 0)//排除机器人
                ->groupBy('ClientType')//注册来源：1、andriod , 2、ios , 3、pc
                ->get();
            $list[$v]['date'] = $v;
            foreach ($register_num[$k] as $key => $val) {
                if ($val['ClientType'] == 1) {
                    $list[$v]['android_sum'] = $val['total'];
                } elseif ($val['ClientType'] == 2) {
                    $list[$v]['ios_sum'] = $val['total'];
                } elseif ($val['ClientType'] == 3) {
                    $list[$v]['pc_sum'] = $val['total'];
                }
            }
            //投注人数
            $logon_num[$v]            = RecordScoreDaily::where('UpdateDate', $v)->count('UserID');
            $registers[$v]            = AccountsInfo::where('RegisterDate', '>=', $v . ' 00:00:00')
                ->where('RegisterDate', '<=', $v . ' 23:59:59')->where('IsAndroid', 0)->count('UserID');
            $list[$v]['login_sum']    = $logon_num[$v];
            $list[$v]['register_sum'] = $registers[$v];
        }
        return ResponeSuccess('请求成功', $list);
    }
    /**
     * 每日统计-盈利统计
     * @return Response
     */
    // 盈利统计(输赢改为：平台盈利=投注-中奖,有效投注，流水,充值量,总营收额)
    public function taxation_statistics(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        //统计输赢、有效投注、流水、税收
        $score_list = RecordScoreDaily::select('UpdateDate as date',
            \DB::raw('sum(JettonScore-RewardScore) as ChangeScore'),//输赢改为：平台盈利=投注-中奖
            \DB::raw('sum(JettonScore) as JettonScore'),//有效投注
            \DB::raw('sum(StreamScore) as StreamScore'),//流水
            \DB::raw('sum(SystemServiceScore) as SystemServiceScore')//税收
        )
        ->andFilterBetweenWhere('UpdateDate',$start_date,$end_date)
        ->groupBy('UpdateDate')->get()->keyBy('date');
        //统计首充人数
        $first_list = FirstRechargeLogs::select(
            \DB::raw('count(distinct(user_id)) first_num'),
            \DB::raw("format(created_at,'yyyy-MM-dd') as date")
        )
        ->andFilterBetweenWhere('created_at',$start_date,$end_date)
        ->groupBy(\DB::raw("format(created_at,'yyyy-MM-dd')"))->pluck('first_num','date');
        //统计充值
        $payment_list = PaymentOrder::select(
            \DB::raw('sum(amount) pay_amount'),
            \DB::raw("format(created_at,'yyyy-MM-dd') as date")
        )
        ->where('payment_status', PaymentOrder::SUCCESS)
        ->andFilterBetweenWhere('created_at',$start_date,$end_date)
        ->groupBy(\DB::raw("format(created_at,'yyyy-MM-dd')"))->pluck('pay_amount','date');
        //统计
        $withdrawal_list = WithdrawalOrder::select(
            \DB::raw('sum(money) withdrawal_money'),
            \DB::raw("format(created_at,'yyyy-MM-dd') as date")
        )
        ->where('status', WithdrawalOrder::PAY_SUCCESS)
        ->andFilterBetweenWhere('created_at',$start_date,$end_date)
        ->groupBy(\DB::raw("format(created_at,'yyyy-MM-dd')"))->pluck('withdrawal_money','date');
        //数据重组
        $dates      = getDateRange($start_date, $end_date, 30);
        $list = [];
        foreach ($dates as $v){
            $list[$v]['winlose_sum']        = realCoins($score_list[$v]['ChangeScore'] ?? 0);//输赢改为：平台盈利=投注-中奖
            $list[$v]['jetton_score']       = realCoins($score_list[$v]['JettonScore'] ?? 0);//有效投注
            $list[$v]['flowing_water']      = realCoins($score_list[$v]['StreamScore'] ?? 0);//流水
            $list[$v]['SystemServiceScore'] = realCoins($score_list[$v]['SystemServiceScore'] ?? 0);//税收
            $list[$v]['first_pay']          = $first_list[$v] ?? 0;//首充人数
            $list[$v]['pay_sum']            = $payment_list[$v] ?? 0;//充值
            $list[$v]['withdraw_sum']       = $withdrawal_list[$v] ?? 0;
            $list[$v]['total_revenue']      = $list[$v]['pay_sum'] - $list[$v]['withdraw_sum'];//总营收额
        }
        return ResponeSuccess('请求成功', $list);
    }

    /**
     * 留存率
     *
     * @return Response
     */
    public function retention_rate(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $dates      = getDateRange($start_date, $end_date, 30);
        //注册人数
        $list       = AccountsInfo::select(
            \DB::raw("CONVERT(varchar(100), RegisterDate, 23) as create_time"),
            \DB::raw('COUNT(UserID) as sum_total')
        )
            ->where('IsAndroid', 0)
            ->andFilterBetweenWhere('RegisterDate', $start_date, $end_date)
            ->groupBy(\DB::raw("CONVERT(varchar(100), RegisterDate, 23)"))
            ->orderBy('create_time', 'desc')
            ->pluck('sum_total', 'create_time');
        //充值人数
        $list1=PaymentOrder::select(
            \DB::raw("CONVERT(varchar(100), success_time, 23) as success_time"),
            \DB::raw('COUNT(DISTINCT user_id) as sum_total')
        )->where('payment_status',PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('success_time', $start_date, $end_date)
            ->groupBy(\DB::raw("CONVERT(varchar(100), success_time, 23)"))
            ->orderBy('success_time', 'desc')
            ->pluck('sum_total', 'success_time');
        $result     = StatisticsRetentions::select('statistics_time', 'type', 'total')->andFilterBetweenWhere('statistics_time', $start_date, $end_date)
            ->orderBy('type')->get();
        $result1     = StatisticsRechargeRates::select('statistics_time', 'type', 'total')->andFilterBetweenWhere('statistics_time', $start_date, $end_date)
            ->orderBy('type')->get();
        $result     = collect($result->toArray())->groupBy('statistics_time');
	    $result1     = collect($result1->toArray())->groupBy('statistics_time');
	    $data       = [];
        foreach ($dates as $k => $v) {
            $type                        = collect($result[$v] ?? [])->keyBy('type');
            $type1                        = collect($result1[$v] ?? [])->keyBy('type');
            $data[$k]['statistics_date'] = $v;
            $data[$k]['register_num']    = $list[$v] ?? 0;
            $data[$k]['recharge_num']    = $list1[$v] ?? 0;
            $data[$k]['next_day']        = $type[1]['total'] ?? 0;
            $data[$k]['third_day']        = $type[2]['total'] ?? 0;
            $data[$k]['seven_days']      = $type[6]['total'] ?? 0;
            $data[$k]['thirty_days']     = $type[29]['total'] ?? 0;
            $data[$k]['recharge_next_day']     = $type1[1]['total'] ?? 0;
            $data[$k]['recharge_third_day']     = $type1[2]['total'] ?? 0;
            $data[$k]['recharge_seven_days']     = $type1[6]['total'] ?? 0;
            $data[$k]['recharge_thirty_days']     = $type1[29]['total'] ?? 0;
        }
        return ResponeSuccess('请求成功', $data);
    }
    /**
     * 渠道留存率
     *
     * @return Response
     */
    public function channel_retention_rate(Request $request)
    {
        Validator::make($request->all(), [
            'channel_id'    => ['nullable', 'numeric'],
        ], [
            'channel_id.numeric' => '渠道ID必须数字',
        ])->validate();
        $end_date   = date('Y-m-d 23:59:59');
        $start_date = date('Y-m-d',strtotime("-30 day"));

        $channel_id = $request->input('channel_id');
        if($channel_id){
            $is_exit=ChannelInfo::where('channel_id',$channel_id)->first();
            if(!$is_exit){
                return ResponeFails('该渠道不存在，请重新输入');
            }
        }
        $dates      = getDateRange($start_date, $end_date, 30);
        //注册人数
        $register_people_total = ChannelUserRelation::from(ChannelUserRelation::tableName().' AS a')
            ->select(\DB::raw('COUNT(*) as total'),\DB::raw('CONVERT(varchar(100), b.RegisterDate, 23) as register_time'))
            ->leftJoin(AccountsInfo::tableName().' AS b','a.user_id','=','b.UserID')
            ->andFilterBetweenWhere('b.RegisterDate', $start_date, $end_date)
            ->groupBy(\DB::raw('CONVERT(varchar(100), b.RegisterDate, 23)'));
        if($request->input('channel_id')){
            $register_people_total->where('a.channel_id',$request->input('channel_id'));
        }
        $register_people_total=$register_people_total->orderByRaw(\DB::raw('CONVERT(varchar(100), b.RegisterDate, 23)').' desc')->get()->toArray();
        $register_people_total=collect($register_people_total)->keyBy('register_time');
        //充值人数
        $recharge_people_total = ChannelUserRelation::from(ChannelUserRelation::tableName().' AS a')
            ->select(\DB::raw('COUNT(DISTINCT b.user_id) as total'),\DB::raw('CONVERT(varchar(100), b.success_time, 23) as success_time'))
            ->leftJoin(PaymentOrder::tableName().' AS b','a.user_id','=','b.user_id')
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('b.success_time', $start_date, $end_date)
            ->groupBy(\DB::raw('CONVERT(varchar(100), b.success_time, 23)'));
        if($request->input('channel_id')){
            $recharge_people_total->where('a.channel_id',$request->input('channel_id'));
        }
        $recharge_people_total=$recharge_people_total->orderByRaw(\DB::raw('CONVERT(varchar(100), b.success_time, 23)').' desc')->get()->toArray();
        $recharge_people_total=collect($recharge_people_total)->keyBy('success_time');
        //登录留存
        $login_records     = StatisticsRetentionChannels::andFilterBetweenWhere('statistics_time', $start_date, $end_date)
            ->select('statistics_time', 'type',\DB::raw('SUM(total) AS total'))
            ->groupBy('statistics_time','type');
        if($request->input('channel_id')){
            $login_records->where('channel_id',$request->input('channel_id'));
        }
        $login_records=$login_records ->orderBy('type')->get()->toArray();
        $login_records=collect($login_records)->groupBy('statistics_time');
        //充值留存
        $recharge_records     = StatisticsRechargeChannels::andFilterBetweenWhere('statistics_time', $start_date, $end_date)
            ->select('statistics_time', 'type',\DB::raw('SUM(total) AS total'))
            ->groupBy('statistics_time','type');
        if($request->input('channel_id')){
            $recharge_records->where('channel_id',$request->input('channel_id'));
        }
        $recharge_records=$recharge_records ->orderBy('type')->get()->toArray();
        $recharge_records=collect($recharge_records)->groupBy('statistics_time');
        $data       = [];
        foreach ($dates as $k => $v) {
            $type                        = collect($login_records[$v] ?? [])->keyBy('type');
            $type1                       = collect($recharge_records[$v] ?? [])->keyBy('type');
            $data[$k]['statistics_date'] = $v;
            $data[$k]['channel_id']      = $channel_id ?? '所有';
            $data[$k]['register_num']    = $register_people_total[$v]['total'] ?? 0;
            $data[$k]['recharge_num']    = $recharge_people_total[$v]['total'] ?? 0;
            $data[$k]['next_day']        = $type[1]['total'] ?? 0;
            $data[$k]['third_day']       = $type[2]['total'] ?? 0;
            $data[$k]['seven_days']      = $type[6]['total'] ?? 0;
            $data[$k]['thirty_days']     = $type[29]['total'] ?? 0;
            $data[$k]['recharge_next_day']     = $type1[1]['total'] ?? 0;
            $data[$k]['recharge_third_day']     = $type1[2]['total'] ?? 0;
            $data[$k]['recharge_seven_days']     = $type1[6]['total'] ?? 0;
            $data[$k]['recharge_thirty_days']     = $type1[29]['total'] ?? 0;
        }
        return ResponeSuccess('请求成功', $data);
    }

    /**
     * 每日流水统计
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     *
     */
    public function statics_flow(Request $request)
    {
        if (!$request->input('user_id')) {
            return ResponeFails('缺少参数');
        }
        $statics = new StaticsService();

        return ResponeSuccess('请求成功', $statics->staticsOrder($request->input('user_id'), Carbon::now()));
    }

    /*每日统计-盈利统计（二级统计）*/
    public function winCount()
    {
        \Validator::make(request()->all(), [
            'date' => ['required', 'date'],
        ], [
            'date.required' => '日期不能为空',
            'date.date'     => '无效日期',
        ])->validate();
        $date = \request('date');
        //统计充值人数、充值笔数
        $payment = PaymentOrder::select(\DB::raw('count(distinct(user_id)) as pay_people'), \DB::raw('count(user_id) as pay_num'))
            ->where('payment_status',PaymentOrder::SUCCESS)->whereDate('created_at',$date)->first();
        //统计人数、笔数
        $withdrawal = WithdrawalOrder::select(\DB::raw('count(distinct(user_id)) as withdrawal_people'), \DB::raw('count(user_id) as withdrawal_num'))
            ->where('status',WithdrawalOrder::PAY_SUCCESS)->whereDate('created_at',$date)->first();
        //统计首充
        $first_pay = FirstRechargeLogs::whereDate('created_at',$date)->sum('coins');
        //有效投注人数
        $bet_pepole = RecordScoreDaily::whereDate('UpdateDate',$date)->distinct('UserID')->count();
        $data = [
            'pay_people'        => $payment['pay_people'],
            'pay_num'           => $payment['pay_num'],
            'withdrawal_people' => $withdrawal['withdrawal_people'],
            'withdrawal_num'    => $withdrawal['withdrawal_num'],
            'first_pay_money'   => realCoins($first_pay),
            'bet_pepole'        => $bet_pepole
        ];
        return ResponeSuccess('请求成功', $data);
    }

}
