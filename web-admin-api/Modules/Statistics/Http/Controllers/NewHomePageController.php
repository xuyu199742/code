<?php
//首页统计
namespace Modules\Statistics\Http\Controllers;

use App\Exceptions\NewException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\GameCategoryRelation;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\OuterPlatformGame;
use Models\Agent\ChannelWithdrawRecord;
use Models\Agent\AgentWithdrawRecord;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;
use PHPUnit\Exception;
use Transformers\ActivityCashDetailsTransformer;

class NewHomePageController extends Controller
{
    //净盈利和税费
    public function getProfit(){
        $start_date = date('Y-m-d',strtotime('-6 days'));
        $end_date = date('Y-m-d');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $platform_profit = RecordScoreDaily::select(
            DB::raw("UpdateDate as create_time"),
            DB::raw('sum(JettonScore-RewardScore) as platform_profit'),  //平台盈利=投注-中奖
            DB::raw('sum(SystemServiceScore) as SystemServiceScore')  //税费
        )
            ->andFilterBetweenWhere('UpdateDate', $start_date, $end_date)
            ->groupBy('UpdateDate')
            ->orderBy('UpdateDate','asc')
            ->get()->toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $cash_gift = RecordTreasureSerial::select(
                DB::raw('sum(ChangeScore) as ChangeScore')  //领取的活动礼金
            )
                ->where('ChangeScore', '>', 0)
                ->whereIn('TypeID', array_keys(RecordTreasureSerial::getTypes(2)))
                ->andFilterBetweenWhere('CollectDate',$value,$value)
                ->first()->toArray();
            $list[$value] = [
                'net_profit' => 0 - realCoins($cash_gift['ChangeScore']),
                'SystemServiceScore' => 0,
            ];
            foreach ($platform_profit as $item){
                if($item['create_time'] == $value){
                    $list[$value] = [
                        'net_profit' => realCoins($item['platform_profit']-$cash_gift['ChangeScore']),
                        'SystemServiceScore' => realCoins($item['SystemServiceScore'])
                    ];
                }
            }
        }
        $res = [];
        $res['chart'] = $list;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d',strtotime('-1 day'));
        //今日新增人数
        $today_total = $this->registerInfo($today);
        $yesterday_total = $this->registerInfo($yesterday);
        $res['today_register_total'] = $today_total['total'];
        //昨日新增人数
        $res['yesterda_register_total'] = $yesterday_total['total'];
        //日环比 =（今日新增-昨日新增）/昨日新增*100%  显示昨日：今日： 昨日为0和大于1000% 用-表示
        if($yesterday_total['total']>0){
            $res['daily_ring_ratio'] = bcadd((($today_total['total']-$yesterday_total['total'])/$yesterday_total['total'])*100,0,2) ?? 0;
            if($res['daily_ring_ratio'] > 1000){
                $res['daily_ring_ratio'] = '-';
            }
        }else{
            $res['daily_ring_ratio'] = '-';
        }
        //今日注册充值总额和今日注册充值最大
        $today_register_recharge = AccountsInfo::from(AccountsInfo::tableName() . ' AS a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                DB::raw("SUM(b.amount) as today_sum_amount"),
                DB::raw("COUNT(distinct b.user_id) as today_sum_total"),
                DB::raw('MAX(b.amount) as today_max_amount')

            )->where('a.IsAndroid', 0)
            ->where('a.RegisterDate','>=',$today)
            ->where('b.payment_status', PaymentOrder::SUCCESS)
            ->first()->toArray();
        $res['today_sum_total'] = $today_register_recharge['today_sum_total'] ?? 0;
        $res['today_sum_amount'] = $today_register_recharge['today_sum_amount'] ?? 0;
        $res['today_max_amount'] = $today_register_recharge['today_max_amount'] ?? 0;
        return ResponeSuccess('请求成功', $res);
    }
    //注册信息（新增人数）
    function registerInfo($time){
        $data = AccountsInfo::select(\DB::raw('COUNT(UserID) as total'))
            ->where('IsAndroid', 0)
            ->andFilterBetweenWhere('RegisterDate', $time, $time)
            ->first();
        return $data;
    }
    //充值
    public function getRecharge(){
        $start_date = date('Y-m-d',strtotime('-6 days'));
        $end_date = date('Y-m-d');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $recharge_info = PaymentOrder::select(
            DB::raw("CONVERT(varchar(100), created_at, 23) as create_time"),
            DB::raw("SUM(amount) as sum_amount"),
            DB::raw("COUNT(distinct user_id) as recharge_total")
        )
            ->where('payment_status', PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('created_at',$start_date, $end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), created_at, 23)"))
            ->get()->toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $list[$value] = [
                'sum_amount' => 0,
                'recharge_total' => 0,
            ];
            foreach ($recharge_info as $item){
                if($item['create_time'] == $value){
                    $list[$value] = [
                        'sum_amount' => $item['sum_amount'] ?? 0,
                        'recharge_total' => $item['recharge_total'] ?? 0,
                    ];
                }
            }
        }
        return ResponeSuccess('请求成功', $list);
    }

    public function getWithdrawal(){
        $start_date = date('Y-m-d',strtotime('-6 days'));
        $end_date = date('Y-m-d');
        $dates = array_reverse(getDateRange($start_date,$end_date));

        $withdrawal_info = WithdrawalOrder::select(
            DB::raw("CONVERT(varchar(100), created_at, 23) as create_time"),
            DB::raw("SUM(money) as sum_real_money"),
            DB::raw("COUNT(distinct user_id) as withdrawal_total")
        )
            ->where('status', WithdrawalOrder::PAY_SUCCESS)
            ->andFilterBetweenWhere('complete_time',$start_date, $end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), created_at, 23)"))
            ->get()->toArray();
        //渠道
        $channel_withdraw = ChannelWithdrawRecord::select(
            \DB::raw("CONVERT(varchar(100), updated_at, 23) as create_time"),
            \DB::raw('SUM(value) as sum_real_money'),
            \DB::raw('COUNT(DISTINCT channel_id) as withdrawal_total')
        )
            ->where('status',ChannelWithdrawRecord::PAY_SUCCESS)
            ->andFilterBetweenWhere('updated_at', $start_date, $end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), updated_at, 23)"))
            ->get()->toArray();
        //代理
        $agent_withdraw = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(AgentWithdrawRecord::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw("CONVERT(varchar(100), b.updated_at, 23) as create_time"),
                \DB::raw('SUM(b.score) as sum_real_money'),
                \DB::raw('COUNT(DISTINCT b.user_id) as withdrawal_total')
            )
            ->where('b.status',AgentWithdrawRecord::PAY_SUCCESS)
            ->andFilterBetweenWhere('b.updated_at', $start_date, $end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), b.updated_at, 23)"))
            ->get()->toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $list[$value] = [
                'sum_real_money' => 0,
                'withdrawal_total' => 0,
            ];
            foreach ($withdrawal_info as $item){
                if($item['create_time'] == $value){
                    $list[$value]['sum_real_money'] = $item['sum_real_money'] ?? 0;
                    $list[$value]['withdrawal_total'] = $item['withdrawal_total'] ?? 0;
                }
            }
            foreach ($channel_withdraw as $item){
                if($item['create_time'] == $value){
                    $list[$value]['sum_real_money'] += $item['sum_real_money'] ?? 0;
                    $list[$value]['withdrawal_total'] += $item['withdrawal_total'] ?? 0;
                }
            }
            foreach ($agent_withdraw as $item){
                if($item['create_time'] == $value){
                    $list[$value]['sum_real_money'] += realCoins($item['sum_real_money']) ?? 0;
                    $list[$value]['withdrawal_total'] += $item['withdrawal_total'] ?? 0;
                }
            }
        }
        return ResponeSuccess('请求成功', $list);
    }
    //活动礼金
    public function getActivityGift(){
        $start_date = date('Y-m-d',strtotime('-6 days'));
        $end_date = date('Y-m-d');
        $dates = array_reverse(getDateRange($start_date,$end_date));
        $activity_gift = RecordTreasureSerial::select(
            DB::raw("CONVERT(varchar(100), CollectDate, 23) as create_time"),
            DB::raw('sum(ChangeScore) as ChangeScore'),  //领取的活动礼金
            DB::raw('COUNT(DISTINCT UserID) as cash_gift_total')//领取人数
        )
            ->where('ChangeScore', '>', 0)
            ->whereIn('TypeID',array_keys(RecordTreasureSerial::getTypes(2)))
            ->andFilterBetweenWhere('CollectDate',$start_date,$end_date)
            ->groupBy(DB::raw("CONVERT(varchar(100), CollectDate, 23)"))
            ->get()->toArray();
        $list = [];
        foreach ($dates as $key => $value){
            $list[$value] = [
                'sum_activity_gift' => 0,
                'activity_gift_total' => 0,
            ];
            foreach ($activity_gift as $item){
                if($item['create_time'] == $value){
                    $list[$value] = [
                        'sum_activity_gift' => realCoins($item['ChangeScore'] ?? 0),
                        'activity_gift_total' => $item['cash_gift_total'] ?? 0,
                    ];
                }
            }
        }
        return ResponeSuccess('请求成功', $list);
    }
    //游戏数据
    public function getGameData(){
        try {
            \Validator::make(request()->all(), [
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
            ], [
                'start_date.date' => '时间格式有误',
                'end_date.date' => '时间格式有误',
            ])->validate();
            //默认选中昨天
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $start_date = request('start_date') ?? $yesterday;
            $end_date = request('end_date') ?? $yesterday;
            $category_games = GameCategory::from(GameCategory::tableName() . ' AS a')
                ->leftJoin(GameCategoryRelation::tableName() . ' AS b', 'a.id', '=', 'b.category_id')
                ->leftJoin(OuterPlatform::tableName() . ' AS c', 'c.id', '=', 'b.platform_id')
                ->where([
                    'a.status' => 1,
                    'a.tag' => 0,
                    'b.status' => 1,
                    'c.status' => 1
                ])
                ->select('a.id AS category_id', 'a.name AS category_name', 'c.id AS platform_id', 'c.name AS platform_name')->get()->toArray();
            $data = RecordGameScore::select(
                    'PlatformID',
                    \DB::raw('sum(ChangeScore) AS sum_profit'),
                    \DB::raw('sum(JettonScore) AS sum_jetton_score')
                )
                ->andFilterBetweenWhere('UpdateTime', $start_date, $end_date)
                ->groupBy('PlatformID')
                ->get()->toArray();

            $category = array_column($category_games, 'category_name','category_id');
            $category_new = [];
            foreach ($category as $k => $v) {
                $category_new[$k]['category_name'] = $v;
                foreach ($category_games as $v2){
                    if($k == $v2['category_id']) {
                        $category_new[$k]['platform_ids'][$v2['platform_id']] = $v2['platform_name'];
                    }
                }
            }
            $res = [];
            foreach ($category_new as $key => $val) {
                $sum_jetton_score = $sum_profit = 0;
                $chart = [];
                $platform_ids = array_keys($val['platform_ids']);
                foreach ($data as $val2){
                    if(in_array($val2['PlatformID'], $platform_ids)){
                        $chart[] = [
                            'category_id' => $key,
                            'name' =>  $val['platform_ids'][$val2['PlatformID']],
                            'platform_id' => $val2['PlatformID'],
                            'sum_profit' => realCoins($val2['sum_profit']),
                            'sum_jetton_score' => realCoins($val2['sum_jetton_score'])
                        ];
                        $sum_jetton_score += $val2['sum_jetton_score'];
                        $sum_profit += $val2['sum_profit'];
                    }
                }
                array_multisort(array_column($chart, 'sum_profit'), SORT_DESC, $chart);
                $res[] = [
                    'category_name' => $val['category_name'],
                    'sum_profit' => realCoins($sum_profit),
                    'sum_jetton_score' => realCoins($sum_jetton_score),
                    'chart' => $chart
                ];
            }
            array_multisort(array_column($res, 'sum_profit'), SORT_DESC, $res);
            return ResponeSuccess('请求成功', $res);
        } catch (NewException $e) {
            \Log::error('首页/游戏数据：'.$e);
            return ResponeFails('数据请求失败');
        }
    }
    //活动礼金-领取详情
    public function getCashGiftDetails(){
        \Validator::make(request()->all(), [
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'start_date.date'           => '时间格式有误',
            'end_date.date'             => '时间格式有误',
        ])->validate();
        //默认选中今天
        $start_date = request('start_date') ?? date('Y-m-d');
        $end_date = request('end_date') ?? date('Y-m-d');
        $list = RecordTreasureSerial::select(
                'TypeID',
                \DB::raw('SUM(ChangeScore) as sum_score'),//操作变化金币
                \DB::raw('COUNT(DISTINCT UserID) as total')//领取人数
            )
            ->where('ChangeScore', '>', 0)
            ->whereIn('TypeID', array_keys(RecordTreasureSerial::getTypes(2)))
            ->andFilterBetweenWhere('CollectDate',$start_date,$end_date)
            ->groupBy('TypeID')
            ->orderBy(\DB::raw('SUM(ChangeScore)'),'desc')
            ->paginate(5);
        return $this->response->paginator($list, new ActivityCashDetailsTransformer());
    }
    //3种设备，各设备登录游戏的人数统计
    public function getLogonPeopleTotal(){
        \Validator::make(request()->all(), [
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'start_date.date'           => '时间格式有误',
            'end_date.date'             => '时间格式有误',
        ])->validate();
        //默认选中今天
        $start_date = request('start_date') ?? date('Y-m-d');
        $end_date = request('end_date') ?? date('Y-m-d');
        $list = RecordUserLogon::from('RecordUserLogon as a')
            ->leftJoin(AccountsInfo::tableName().' as b','a.UserID','=','b.UserID')
            ->select('b.ClientType',
                \DB::raw('COUNT(DISTINCT a.UserID) as total')//登录游戏的人数
            )
            ->where('b.ClientType','<>','')
            ->andFilterBetweenWhere('a.CreateDate',$start_date,$end_date)
            ->groupBy('b.ClientType')   //注册来源：1、android，2、ios，3、pc
            ->orderBy(\DB::raw('COUNT(DISTINCT a.UserID)'),'desc')
            ->get()->toArray();
        $res=[];
        if($list && count($list)>0){
            $sum_total = array_sum(array_column($list,'total'));
            foreach ($list as $k=>$v){
                switch ($v['ClientType']) {
                    case 1:
                        $res[$k]['ClientType'] = '安卓';
                        break;
                    case 2:
                        $res[$k]['ClientType'] = '苹果';
                        break;
                    default:
                        $res[$k]['ClientType'] = 'H5';
                }
                $res[$k]['total'] = $v['total'];
                $res[$k]['ratio'] = bcadd(($v['total']/$sum_total)*100,0,2) ?? 0;
            }
            $result['chart'] = $res;
            $result['sum_total'] = $sum_total;
        }else{
            $result = [
                'chart' =>[
                    [
                        'ClientType'=> "安卓",
                        'total'=> 0,
                        'ratio'=> 0,
                    ],
                    [
                        'ClientType'=> "苹果",
                        'total'=> 0,
                        'ratio'=> 0,
                    ],
                    [
                        'ClientType'=> "H5",
                        'total'=> 0,
                        'ratio'=> 0,
                    ],
                ],
                'sum_total' => 0,
            ];
        }
        return ResponeSuccess('请求成功', $result);
    }

}
