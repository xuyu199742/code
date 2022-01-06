<?php
/**
 * Created by PhpStorm.
 * User: 86181
 * Date: 2020/4/13
 * Time: 13:55
 */

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentRelation;
use Models\Agent\AgentWithdrawRecord;
use Models\Agent\ChannelUserRelation;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\OuterPlatformGame;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Modules\System\Http\Requests\CashGiftsRequest;
use Modules\System\Http\Requests\BettingReportRequest;
use Modules\System\Http\Requests\FirstRechargeRequest;
use Modules\System\Http\Requests\JettonAnalysisRequest;
use Modules\System\Http\Requests\RechargeWithdrawal;
use Transformers\AnalysisCashGiftsTransformer;
use Transformers\FirstRechargeTransformer;
use DB;
use Transformers\JettonAnalysisTransformer;
use Transformers\RechargeWithdrawalTransformer;

class AnalysisController extends Controller
{
    /**
     * 首页优化 - 首充
     *
     */
    public function firstRecharge(FirstRechargeRequest $request){
        //首充
        $first = PaymentOrder::query()->from(PaymentOrder::tableName().' as a')->selectRaw('a.user_id,count(*) as first_count,a.amount as first_amount,a.created_at as first_date')
            ->join(DB::raw('(select min(id) as min_id from '.PaymentOrder::tableName()." where payment_status = '".PaymentOrder::SUCCESS."' group by user_id) as b"),'a.id','=','b.min_id')
            ->groupBy('a.user_id','a.amount','a.created_at');
        //充值
        $recharge = PaymentOrder::from(PaymentOrder::tableName())->selectRaw('user_id,count(*) as recharge_count,sum(amount) as recharge_amount')->where('payment_status',PaymentOrder::SUCCESS)->groupBy('user_id');

        $withdrawal = WithdrawalOrder::from(WithdrawalOrder::tableName())->selectRaw('user_id,count(*) as withdrawal_count,sum(money) as withdrawal_amount')->where('status',WithdrawalOrder::PAY_SUCCESS)->groupBy('user_id');
        //代理
        $agent_withdrawal = AgentWithdrawRecord::from(AgentWithdrawRecord::tableName())->selectRaw('user_id,count(*) as agent_count,sum(score) as agent_score')->where('status',AgentWithdrawRecord::PAY_SUCCESS)->andFilterBetweenWhere('created_at',$request->start_date,$request->end_date)->groupBy('user_id');
        $init_query = AccountsInfo::query()->from(AccountsInfo::tableName().' as a')
            ->select('a.GameID','a.UserID','a.RegisterDate','b.first_amount','b.first_count','b.first_date','c.recharge_count','c.recharge_amount','e.channel_id','f.parent_user_id',
                'd.withdrawal_count','x.agent_count','d.withdrawal_amount','x.agent_score'
            )
            ->leftJoinSub($first,'b','a.UserID','=','b.user_id')
            ->leftJoinSub($recharge,'c','a.UserID','=','c.user_id')
            ->leftJoinSub($withdrawal,'d','a.UserID','=','d.user_id')
            ->leftJoinSub($agent_withdrawal,'x','a.UserID','=','x.user_id')
            ->leftJoin(ChannelUserRelation::tableName().' as e','a.UserID','=','e.user_id')
            ->leftJoin(AgentRelation::tableName().' as f','a.UserID','=','f.user_id')
            ->where('isAndroid',0)
            ->andFilterWhere('a.GameID',$request->game_id)
            ->andFilterWhere('e.channel_id',$request->channel_id)
            ->andFilterBetweenWhere('a.RegisterDate',$request->register_start_date,$request->register_end_date)
            ->andFilterBetweenWhere('b.first_date',$request->first_start_date,$request->first_end_date)
            ->andFilterIntervalWhere('b.first_amount',$request->first_min,$request->first_max)
            ->andFilterIntervalWhere('c.recharge_amount',$request->recharge_min,$request->recharge_max)
            ->where(function ($query)use($request){
                $query->orWhere(function ($q)use($request){
                    $q->andFilterIntervalWhere('d.withdrawal_amount',$request->withdrawal_min,$request->withdrawal_max);
                })->orWhere(function ($q)use($request){
                    $withdrawal_min = is_numeric($request->withdrawal_min) ? $request->withdrawal_min * realRatio() : '';
                    $withdrawal_max = is_numeric($request->withdrawal_max) ? $request->withdrawal_max * realRatio() : '';
                    $q->andFilterIntervalWhere('x.agent_score',$withdrawal_min,$withdrawal_max);
                });
            })
            ->orderBy('b.first_amount','desc');
        $sum = clone $init_query;
        $query = $init_query->groupBy('a.GameID','a.UserID','a.RegisterDate','b.first_amount','b.first_count','b.first_date','c.recharge_count','c.recharge_amount','d.withdrawal_count','d.withdrawal_amount','e.channel_id','f.parent_user_id','x.agent_count','x.agent_score');
        $total = [
            "first_people_total" => $sum->sum('b.first_count'),
            "first_amount_total" => bcadd($sum->sum('b.first_amount'),0),
            "recharge_people_total" => $sum->sum('c.recharge_count'),
            "recharge_amount_total" => bcadd($sum->sum('c.recharge_amount'),0)
        ];
        $list = $query->paginate(15);
        return $this->response->paginator($list,new FirstRechargeTransformer())->addMeta('total',$total);
    }

    /**
     * 数据分析 - 充值
     *
     */
    public function rechargeWithdrawal(RechargeWithdrawal $request){
        $sort_order = request('sort_order','desc');
        switch ($request->sort_field){
            case 'recharge_count':
                $sort_field = 'c.recharge_count';
                break;
            case 'recharge_amount':
                $sort_field = 'c.recharge_amount';
                break;
            case 'withdrawal_count':
                $sort_field = 'd.withdrawal_count';
                break;
            case 'withdrawal_amount':
                $sort_field = 'd.withdrawal_amount';
                break;
            case 'balance':
                $sort_field = '(f.Score + f.InsureScore)';
                break;
            case 'change_score':
                $sort_field = DB::raw('isnull(b.change_score,0)');
                break;
            default:
                $sort_field = 'c.recharge_amount';
                break;
        }
        //用户记录
        $record = RecordGameScore::from(RecordGameScore::tableName())->selectRaw('UserID,sum(ChangeScore) as change_score')->groupBy('UserID');
        //充值
        $recharge = PaymentOrder::from(PaymentOrder::tableName())->selectRaw('user_id,count(*) as recharge_count,sum(amount) as recharge_amount')->where('payment_status',PaymentOrder::SUCCESS)->andFilterBetweenWhere('created_at',$request->start_date,$request->end_date)->groupBy('user_id');
        $withdrawal = WithdrawalOrder::from(WithdrawalOrder::tableName())->selectRaw('user_id,count(*) as withdrawal_count,sum(money) as withdrawal_amount')->where('status',WithdrawalOrder::PAY_SUCCESS)->andFilterBetweenWhere('created_at',$request->start_date,$request->end_date)->groupBy('user_id');
        //代理
        $agent_withdrawal = AgentWithdrawRecord::from(AgentWithdrawRecord::tableName())->selectRaw('user_id,count(*) as agent_count,sum(score) as agent_score')->where('status',AgentWithdrawRecord::PAY_SUCCESS)->andFilterBetweenWhere('created_at',$request->start_date,$request->end_date)->groupBy('user_id');
        //手动上分
        $up = RecordTreasureSerial::from(RecordTreasureSerial::tableName())->selectRaw('UserID,count(*) as system_up_count,sum(ChangeScore) as system_up_amount')->where('TypeID',RecordTreasureSerial::SYSTEM_GIVE_TYPE)->where('ChangeScore','>',0)->andFilterBetweenWhere('CollectDate',$request->start_date,$request->end_date)->groupBy('UserID');
        //手动下分
        $down = RecordTreasureSerial::from(RecordTreasureSerial::tableName())->selectRaw('UserID,count(*) as system_down_count,sum(ChangeScore) as system_down_amount')->where('TypeID',RecordTreasureSerial::SYSTEM_GIVE_TYPE)->where('ChangeScore','<',0)->andFilterBetweenWhere('CollectDate',$request->start_date,$request->end_date)->groupBy('UserID');
        //联合取充值或有值的记录
        $up_user = RecordTreasureSerial::from(RecordTreasureSerial::tableName())->selectRaw('UserID as user_id')->where('TypeID',RecordTreasureSerial::SYSTEM_GIVE_TYPE)->where('ChangeScore','>',0)->andFilterBetweenWhere('CollectDate',$request->start_date,$request->end_date)->groupBy('UserID');
        $down_user = RecordTreasureSerial::from(RecordTreasureSerial::tableName())->selectRaw('UserID as user_id')->where('TypeID',RecordTreasureSerial::SYSTEM_GIVE_TYPE)->where('ChangeScore','<',0)->andFilterBetweenWhere('CollectDate',$request->start_date,$request->end_date)->groupBy('UserID');
        $recharge_user = PaymentOrder::from(PaymentOrder::tableName())->select('user_id')->where('payment_status',PaymentOrder::SUCCESS)->andFilterBetweenWhere('created_at',$request->start_date,$request->end_date)->groupBy('user_id');
        $agent_user = AgentWithdrawRecord::from(AgentWithdrawRecord::tableName())->select('user_id')->where('status',AgentWithdrawRecord::PAY_SUCCESS)->andFilterBetweenWhere('created_at',$request->start_date,$request->end_date)->groupBy('user_id');
        $u_d_r_w_a_union = WithdrawalOrder::query()->from(WithdrawalOrder::tableName())
            ->select('user_id')
            ->where('status',WithdrawalOrder::PAY_SUCCESS)
            ->andFilterBetweenWhere('created_at',$request->start_date,$request->end_date)
            ->groupBy('user_id')
            ->union($recharge_user)->union($down_user)->union($up_user)->union($agent_user);
        $init_query = AccountsInfo::query()->from(AccountsInfo::tableName().' as a')
            ->select('a.GameID','a.RegisterMobile','a.UserID','b.change_score','c.recharge_count','c.recharge_amount','d.withdrawal_count',
                'd.withdrawal_amount','e.channel_id','s1.system_up_count','s1.system_up_amount','s2.system_down_count','s2.system_down_amount',
                'x.agent_count','x.agent_score',DB::raw('(f.Score + f.InsureScore) as balance')
            );
        if($request->game_id){
            $gameIds = explode(',',$request->game_id);
            $userIds = AccountsInfo::query()->select('UserID')->whereIn('GameID',$gameIds);
            $init_query->joinSub($userIds,'u2','a.UserID','=','u2.UserID');
        }
        $init_query->joinSub($u_d_r_w_a_union,'u','a.UserID','=','u.user_id')
            ->leftJoinSub($record,'b','a.UserID','=','b.UserID')
            ->leftJoinSub($recharge,'c','a.UserID','=','c.user_id')
            ->leftJoinSub($withdrawal,'d','a.UserID','=','d.user_id')
            ->leftJoinSub($up,'s1','a.UserID','=','s1.UserID')
            ->leftJoinSub($down,'s2','a.UserID','=','s2.UserID')
            ->leftJoinSub($agent_withdrawal,'x','a.UserID','=','x.user_id')
            ->leftJoin(ChannelUserRelation::tableName().' as e','a.UserID','=','e.user_id')
            ->leftJoin(GameScoreInfo::tableName().' as f','a.UserID','=','f.UserID')
            ->where('isAndroid',0)
            ->andFilterWhere('e.channel_id',$request->channel_id)
            ->andFilterIntervalWhere('c.recharge_amount',$request->recharge_amount_min,$request->recharge_amount_max)
            ->andFilterIntervalWhere('c.recharge_count',$request->recharge_count_min,$request->recharge_count_max)
            ->where(function ($query)use($request){
                $query->orWhere(function ($q)use($request){
                    $q->andFilterIntervalWhere('d.withdrawal_amount',$request->withdrawal_amount_min,$request->withdrawal_amount_max)
                        ->andFilterIntervalWhere('d.withdrawal_count',$request->withdrawal_count_min,$request->withdrawal_count_max);
                })->orWhere(function ($q)use($request){
                    $withdrawal_amount_min = is_numeric($request->withdrawal_amount_min) ? $request->withdrawal_amount_min * realRatio() : '';
                    $withdrawal_amount_max = is_numeric($request->withdrawal_amount_max) ? $request->withdrawal_amount_max * realRatio() : '';
                    $q->andFilterIntervalWhere('x.agent_score',$withdrawal_amount_min,$withdrawal_amount_max)
                        ->andFilterIntervalWhere('x.agent_count',$request->withdrawal_count_min,$request->withdrawal_count_max);
                });
            })
//            ->where(function ($query){
//                $query->orWhereNotNull('c.recharge_amount')->orWhereNotNull('d.withdrawal_amount')->orWhereNotNull('s1.system_up_amount')->orWhereNotNull('s2.system_down_amount');
//            })
            ->orderBy(DB::raw($sort_field),$sort_order);
        $sum = clone $init_query;
        $query = $init_query->groupBy('a.GameID','a.RegisterMobile','a.UserID','b.change_score','c.recharge_count','c.recharge_amount','d.withdrawal_count','d.withdrawal_amount','e.channel_id','f.Score','f.InsureScore','s1.system_up_count','s1.system_up_amount','s2.system_down_count','s2.system_down_amount','x.agent_count','x.agent_score');
        $total = [
            "recharge_people_total" => $sum->sum('recharge_count'),
            "recharge_amount_total" => bcadd($sum->sum('recharge_amount'),0),
            "withdrawal_people_total" => $sum->sum('withdrawal_count') + $sum->sum('agent_count'),
            "withdrawal_amount_total" => bcadd($sum->sum('withdrawal_amount'), realCoins($sum->sum('agent_score')),2),
            "change_score_total" => realCoins($sum->sum('change_score')),
        ];
        $list = $query->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new RechargeWithdrawalTransformer())->addMeta('total',$total);
    }
    /**
     * 数据分析 - 优惠（礼金）
     *
     */
    public function cashGift(CashGiftsRequest $request){
        $start_date = $request->input('start_date') ;
        $end_date = $request->input('end_date');
        $sort_order = request('sort_order','desc');
        $type_id = request('type_id');      //礼金类型
        $analysis_cg_types = array_keys(RecordTreasureSerial::getTypes(2));//统计的所有礼金类型
        switch ($request->sort_field){
            case 'sum_score':
                $sort_field = 'b.cash_gift_score';
                break;
            default:
                $sort_field = 'b.cash_gift_total';
                break;
        }
        //活动礼金
        if($type_id < 0){
            $select_field = "UserID,count(*) as cash_gift_total,sum(ChangeScore) as cash_gift_score";
            $select_fields = "a.GameID,b.cash_gift_total,b.cash_gift_score,c.channel_id";
        }else{
            $select_field = "UserID,TypeID,count(*) as cash_gift_total,sum(ChangeScore) as cash_gift_score";
            $select_fields = "a.GameID,b.TypeID,b.cash_gift_total,b.cash_gift_score,c.channel_id";
        }
        $query = RecordTreasureSerial::from(RecordTreasureSerial::tableName())
            ->selectRaw($select_field)
            ->andFilterBetweenWhere('CollectDate',$start_date,$end_date)
            ->where('ChangeScore','>',0);
        if($type_id < 0){
            $query->whereIn('TypeID',$analysis_cg_types)->groupBy('UserID')->having(\DB::raw('count(*)'),'>','0');
        }else{
            $query->where('TypeID',$type_id)->groupBy('UserID','TypeID')->having(\DB::raw('count(*)'),'>','0');
        }
        $list= AccountsInfo::query()->from(AccountsInfo::tableName().' as a')
            ->selectRaw($select_fields);
        if($request->game_id){
            $gameIds = explode(',',$request->game_id);
            $userIds = AccountsInfo::query()->select('UserID')->whereIn('GameID',$gameIds);
            $list->joinSub($userIds,'u','a.UserID','=','u.UserID');
        }
        $list->leftJoinSub($query,'b','a.UserID','=','b.UserID')
            ->leftJoin(ChannelUserRelation::tableName().' as c','a.UserID','=','c.user_id')
            ->where('isAndroid',0)
            ->whereNotNull('b.cash_gift_total')
            ->andFilterWhere('c.channel_id',$request->channel_id)
            ->andFilterIntervalWhere('b.cash_gift_score',moneyToCoins($request->score_lower), moneyToCoins($request->score_upper))
            ->andFilterIntervalWhere('b.cash_gift_total',$request->total_lower,$request->total_upper)
            ->orderBy(DB::raw($sort_field),$sort_order);
        if($type_id < 0){
            $list->groupBy('a.GameID','c.channel_id','b.cash_gift_total','b.cash_gift_score');
        }else{
            $list->groupBy('a.GameID','c.channel_id','b.TypeID','b.cash_gift_total','b.cash_gift_score');
        }
        $data = clone $list;
        $sum_times = $sum_score = 0;
        $sum_list = RecordTreasureSerial::from(RecordTreasureSerial::tableName().' as a')
            ->leftJoin(ChannelUserRelation::tableName().' as c','a.UserID','=','c.user_id')
            ->where('a.ChangeScore','>',0)
            ->andFilterBetweenWhere('a.CollectDate',$start_date,$end_date)
            ->andFilterWhere('c.channel_id',$request->channel_id);
        if ($type_id > 0){
            $sum_list = $sum_list->where('a.TypeID',$request->type_id);
        }else{
            $sum_list = $sum_list->whereIn('a.TypeID',$analysis_cg_types);
        }
        $sum_times = $sum_list->count();
        $sum_score = $sum_list->sum('a.ChangeScore');
        $res = $data->paginate(config('page.list_rows'));
        return $this->response->paginator($res,new AnalysisCashGiftsTransformer())
            ->addMeta('TypeID',RecordTreasureSerial::ClientType(false,2))
            ->addMeta('sum_times',$sum_times)
            ->addMeta('sum_score',realCoins($sum_score));
    }

    /**
     * 数据分析 - 下注分析
     *
     */
    public function jettonAnalysis(JettonAnalysisRequest $request){
        $sort_order = request('sort_order','desc');
        switch ($request->sort_field){
            case 'note_count':
                $sort_field = 'count(*)';
                break;
            case 'jetton_score':
                $sort_field = 'sum(a.JettonScore)';
                break;
            case 'change_score':
                $sort_field = 'sum(a.ChangeScore)';
                break;
            default:
                $sort_field = 'sum(a.JettonScore)';
                break;
        }
        $platform_name = OuterPlatform::where('id',$request->platform_id)->value('name');
        $jetton_score_min = is_numeric($request->jetton_score_min) ? $request->jetton_score_min * realRatio() : '';
        $jetton_score_max = is_numeric($request->jetton_score_max) ? $request->jetton_score_max * realRatio() : '';
        $change_score_min = is_numeric($request->change_score_min) ? $request->change_score_min * realRatio() : '';
        $change_score_max = is_numeric($request->change_score_max) ? $request->change_score_max * realRatio() : '';
        $userIds = AccountsInfo::query()->from(AccountsInfo::tableName().' as b');
        if($request->game_id){
            $gameIds = explode(',',$request->game_id);
            $userIds->whereIn('GameID',$gameIds);
        }
        $list = RecordGameScore::query()->from(RecordGameScore::tableName().' as a')
            ->select('a.UserID','b.GameID','c.channel_id',
                DB::raw('count(*) as note_count'),
                DB::raw('sum(a.JettonScore) as jetton_score'),
                DB::raw('sum(a.ChangeScore) as change_score')
            )
            ->joinSub($userIds,'b','a.UserID','=','b.UserID')
            ->leftJoin(ChannelUserRelation::tableName().' as c','a.UserID','=','c.user_id')
            ->leftJoin(OuterPlatform::tableName().' as d','a.PlatformID','=','d.id')
            ->andFilterWhere('c.channel_id',$request->channel_id)
            ->andFilterWhere('a.PlatformID',$request->platform_id)
            ->andFilterBetweenWhere('a.UpdateTime',$request->start_date,$request->end_date)
            ->orderBy(DB::raw($sort_field),$sort_order)
            ->groupBy('a.UserID','b.GameID','c.channel_id')
            ->andFilterIntervalHaving(DB::raw('count(*)'),$request->note_count_min,$request->note_count_max)
            ->andFilterIntervalHaving(DB::raw('sum(a.JettonScore)'),$jetton_score_min,$jetton_score_max)
            ->andFilterIntervalHaving(DB::raw('sum(a.ChangeScore)'),$change_score_min,$change_score_max);
        $data = $list->paginate(config('page.list_rows'));
        foreach($data as $item){
            $item->name = $platform_name;
        }
        return $this->response->paginator($data,new JettonAnalysisTransformer());
    }

    /**
     * 数据分析 - 下注明细
     *
     */
    public function jettonDetails(){
        \Validator::make(request()->all(), [
            'game_id'     => ['required','integer'],
            'start_date'  => ['nullable','date'],
            'end_date'    => ['nullable','date'],
        ], [
            'game_id.required'    => '玩家ID必传',
            'game_id.integer'     => '玩家ID必须是整数',
            'start_date.date'     => '开始时间格式不对',
            'end_date.date'       => '结束时间格式不对',
        ])->validate();
        $UserID = (new AccountsInfo())->getUserId(request('game_id'));
        $data = RecordGameScore::query()->from(RecordGameScore::tableName().' as a')
            ->select('a.PlatformID','a.KindID',
                DB::raw('d.name as platform_name'),
                DB::raw('b.name as kind_name'),
                DB::raw('count(*) as note_count'),
                DB::raw('sum(a.JettonScore) as jetton_score'),
                DB::raw('sum(a.ChangeScore) as change_score')
            )
            ->leftJoin(OuterPlatform::tableName().' as d','a.PlatformID','=','d.id')
            ->leftJoin(OuterPlatformGame::tableName().' as b',function ($join){
                $join->on('a.PlatformID','=','b.platform_id')->on('a.KindID','=','b.kind_id');
            })
            ->where('UserID',$UserID)
            ->andFilterBetweenWhere('a.UpdateTime',request('start_date'),request('end_date'))
            ->groupBy('a.PlatformID','a.KindID','d.name','b.name')
            ->get()
            ->groupBy('PlatformID')
            ->map(function ($item){
                $item->map(function ($it){
                    $it->jetton_score = realCoins($it->jetton_score);
                    $it->change_score = realCoins($it->change_score);
                });
                return collect([
                    'platform_note_count'   => $item->sum('note_count'),
                    'platform_jetton_score' => bcadd($item->sum('jetton_score'),0,2),
                    'platform_change_score' => bcadd($item->sum('change_score'),0,2),
                    'platform_name'         => $item[0]->platform_name,
                    'platform_game_list'    => $item
                ]);
            })->toArray();
        $data = array_values($data);
        return ResponeSuccess('请求成功',$data);
    }

    // 投注人数
    public function bettingReport(BettingReportRequest $request)
    {
        $orderBy = $request->order_by_platform ?? null;
        $start =  $request->start_time ?? Carbon::now()->startOfMonth()->toDateTimeString();
        $end = $request->end_time ?? Carbon::now()->endOfMonth()->toDateTimeString();

        if(strtotime($end) > time()) {
            $end = now()->toDateTimeString();
        }
        $res = [];
        $index = -1;
        $platform_list = OuterPlatform::query()->pluck('name', 'id');
        $list = RecordGameScore::query()->from(RecordGameScore::tableName().' as s')
            ->andFilterBetweenWhere('s.UpdateTime', $start, $end)
            ->leftJoin(OuterPlatform::tableName().' as p', 's.PlatformID','=','p.id')
            ->selectRaw('CONVERT(VARCHAR(10), s.UpdateTime, 23) AS date, COUNT(DISTINCT UserID) AS total, p.id as platform_id, p.name')
            ->groupBy(DB::raw('CONVERT(VARCHAR(10), s.UpdateTime, 23)'), 'p.id', 'p.name')
            ->get()
            ->groupBy('date');
        $dates = getDateRange($start, $end);
        foreach ($dates as $date) {
            $index++;
            $res[$index] = [
                'date' => $date,
            ];
            foreach ($platform_list as $key => $name) {
                $platform = $list[$date] ?? null;
                $platform = $platform ? $platform->firstWhere('platform_id', $key) ?? null : null;
                if ($platform) {
                    $res[$index] += [
                        $key => $platform['total'],
                    ];
                } else {
                    $res[$index] += [
                        $key => 0,
                    ];
                }
            }
        }
        if($orderBy) {
            array_multisort(array_column($res, $orderBy), (int)$request->sort ?? SORT_ASC, $res);
        }
        $currentPageItems = collect($res)->slice((request('page',1) * 20) - 20, 20)->values()->all();
        $paginator = new LengthAwarePaginator($currentPageItems, count($res), 20, request('page',1));
        return ResponeSuccess('请求成功', $paginator);
    }
}


