<?php

namespace Modules\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use libs\Tree;
use Models\Accounts\AccountsInfo;
use Models\Accounts\SystemStatusInfo;
use Models\Accounts\UserLevel;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentIncome;
use Models\Agent\AgentIncomeDetails;
use Models\Agent\AgentInfo;
use Models\Agent\AgentRateConfig;
use Models\Agent\AgentRelation;
use Models\Agent\AgentWithdrawRecord;
use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\GameCategoryRelation;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;
use Modules\Client\Http\Requests\AgentWithdraw;
use Modules\Client\Transformers\AgentIncomeResource;
use Modules\Client\Transformers\AgentWithdrawRecordResource;
use Modules\Client\Transformers\MemberDetailsResource;
use Modules\Client\Transformers\MyBrokerageResource;
use Modules\Client\Transformers\PayListResource;
use Modules\Client\Transformers\TeamReportFormsResource;
use Validator;

class AgentController extends Controller
{
    /**
     * 返佣金额表
     *
     */
    public function profitExplain($user_id)
    {
        $game_categorys = GameCategory::select('id','name')->where('tag',GameCategory::GAME_CATEGORY)->orderBy('sort','asc')->get();
        $res=[];
        foreach ($game_categorys as $item=>$value){
            $list =  AgentRateConfig::where('category_id',$value['id'])->orderBy('water_min','asc')->get();
            foreach ($list as $k => $v) {
                 $min_size = $list[$k]['water_min']/100000000;
                 $list[$k]['water_min'] = realCoins($list[$k]['water_min']);
                if(isset($list[$k + 1])) {
                    $list[$k]['water_max'] =realCoins($list[$k+1]['water_min']);
                    if ($min_size < 1){
                        $min_size *= 10000;
                        $str = '+';
                    }else{
                        $str = '万+';
                    }
                    $list[$k]['title'] = $min_size.$str;
                } else {
                    $list[$k]['water_max'] = '上不封顶';
                    if ($min_size < 1){
                        $min_size *= 10000;
                        $str = '+';
                    }else{
                        $str = '万+';
                    }
                    $list[$k]['title'] = $min_size.$str;
                }
               /* $min_size = $list[$k]['water_min'] / 100000000;
                $list[$k]['water_min'] = realCoins($list[$k]['water_min']);
                if (isset($list[$k + 1])) {
                    $list[$k]['water_max'] = realCoins($list[$k + 1]['water_min']);
                    $max_size = $list[$k + 1]['water_min'] / 100000000;
                    if ($min_size < 1) {
                        $min_size *= 10000;
                        $str = '到';
                    } else {
                        $str = '万到';
                    }
                    $list[$k]['title'] = $min_size . $str . $max_size . '万';
                } else {
                    $list[$k]['water_max'] = '上不封顶';
                    $list[$k]['title'] = $min_size . '万以上';
                }*/
            }
            //当前玩家各个分类所在层级（按团队业绩计算）
            $platform_lists = GameCategoryRelation::where('category_id',$value['id'])->pluck('platform_id')->toArray();
            $JettonScore = RecordGameScore::from('RecordGameScore as a')
                ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                ->leftJoin(GameCategoryRelation::tableName().' as c','a.PlatformID','=','c.platform_id')
                ->where('b.rank','like','%,'.$user_id.',%')
                ->whereIn('a.PlatformID',$platform_lists)
                ->andFilterBetweenWhere('a.UpdateTime',date('Y-m-d'),date('Y-m-d'))
                ->sum('JettonScore');
            $res[$item]['category_id'] = $value['id'];
            $res[$item]['name'] = $value['name'];
            $res[$item]['jetton_score'] = realCoins($JettonScore);
            $res[$item]['list'] = $list->toArray();
        }
        return ResponeSuccess('请求成功',$res);
    }
    /**
     * 赚钱说明(新)
     *
     */
    public function newProfitExplain()
    {
        $list = SystemStatusInfo::select('StatusValue','StatusDescription')->find('AgentSettlementWay');
        if(!$list){
            $list['StatusValue'] = 1;
            $list['StatusDescription'] = '键值：代理结算方式，键值：1-流水，2-有效投注';
        }
        return ResponeSuccess('请求成功',$list);
    }
    /**
     * 我的推广（修改所需字段）
     *
     */
    public function profitShare()
    {
        Validator::make(request()->all(), [
                'user_id'      => 'required|integer',
                'type'         => 'required|in:app,h5',
        ], [
            'user_id.required'  => '代理ID必传',
            'user_id.integer'   => '代理ID为整数',
            'type.required'     => '客户端类型必传',
            'type.in'           => '客户端类型不在范围内',
        ])->validate();
        $user_id = request('user_id');
        $user = AccountsInfo::from('AccountsInfo as a')
            ->select('a.UserID','a.GameID','b.balance','c.parent_user_id','d.GameID as parent_gameid')
            ->leftJoin(AgentInfo::tableName().' as b','a.UserID','=','b.user_id')
            ->leftJoin(AgentRelation::tableName().' as c','a.UserID','=','c.user_id')
            ->leftJoin(AccountsInfo::tableName().' as d','d.UserID','=','c.parent_user_id')
            ->where('a.UserID',$user_id)->where('a.IsAndroid',0)->first();
        if (empty($user)){
            return ResponeFails('用户不存在');
        }
        //所属上级
        $list['parent_gameid'] = $user->parent_gameid ?? '';
        //团队总人数
        $list['team_player'] = AgentRelation::where('rank','like','%,'.$user_id.',%')->count() + 1;
        //团队新增人数
        $list['team_player_add'] = AgentRelation::where('rank','like','%,'.$user_id.',%')->whereDate('created_at',date('Y-m-d'))->count();
        //直推总人数
        $list['directly_player'] = AgentRelation::where('parent_user_id',$user_id)->count();
        //直推新增人数
        $list['directly_player_add'] = AgentRelation::where('parent_user_id',$user_id)->whereDate('created_at',date('Y-m-d'))->count();
        //今日团队业绩
        $Income = RecordScoreDaily::from('RecordScoreDaily as a')
            ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
            ->whereDate('a.UpdateDate',date('Y-m-d'))
            ->where('b.rank','like','%,'.$user_id.',%')
            ->sum('a.JettonScore');
        //$Income = AgentIncome::where('user_id',$user_id)->whereDate('start_date',date('Y-m-d'))->first();
        $list['team_performance_today'] = realCoins($Income ?? 0 );
        //今日直推业绩
        $sub_bet_money_total = RecordScoreDaily::from('RecordScoreDaily as a')
            ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
            ->where('b.parent_user_id',$user_id)
            ->whereDate('a.UpdateDate',date('Y-m-d'))
            ->sum('a.JettonScore');
        $list['directly_performance_today'] = realCoins($sub_bet_money_total ?? 0);
        //昨日佣金
        $AgentIncome = AgentIncome::where('user_id',$user_id)->whereDate('start_date',date("Y-m-d",strtotime("-1 day")))->first();
        $list['brokerage_yestrday'] = realCoins(empty($AgentIncome) ? 0 : $AgentIncome->reward_score);
        //历史总佣金
        $brokerage_history = AgentIncome::where('user_id',$user_id)->sum('reward_score');
        $list['brokerage_history'] = realCoins($brokerage_history ?? 0);
        //可提现佣金
        $list['brokerage_balance'] = realCoins($user->balance);
        //下载链接
        $list['download_url'] = getQrcodeUrl(request('type','app')).'?agentid='.$user_id;
        return ResponeSuccess('请求成功',$list);
    }

    /**
    * 个人业绩-直推总览
    *
    * */
    public function personalOverview(Request $request)
    {
        Validator::make(request()->all(), [
            'user_id'         => ['required','numeric'],
            'game_id'         => ['nullable','numeric'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'user_id.required' => '代理ID必传！',
            'game_id.numeric'  => '代理ID必须是数字，请重新输入！',
        ])->validate();
        try{
            /*注册人数，首充人数，下级人数，充值； 投注人数，投注(打码量)，流水，佣金(个人)，活动礼金 ； 充值赠送*/
            $user_id = request('user_id');
            $game_id = request('game_id');
            $user = AccountsInfo::with('agent')->where('UserID',$user_id)->first();
            if (!$user){
                return ResponeFails('用户不存在');
            }
            if($game_id){
                $s_user = AccountsInfo::where('GameID',$game_id)->first();
                if (!$s_user){
                    return ResponeFails('查询的用户不存在');
                }
                $exit_sub_user = AgentRelation::where('parent_user_id',$user_id)->where('user_id',$s_user['UserID'])->pluck('user_id')->toArray();
                if(!$exit_sub_user){
                    return ResponeFails('该用户不属于直推玩家，请重新输入');
                }
            }
            $start_date = request('start_date')??date('Y-m-d',strtotime('-6 days'));
            $end_date   = request('end_date',date('Y-m-d'));
            if($end_date){
            	$end_date=date('Y-m-d 23:59:59',strtotime($end_date));
            }
            //注册人数（已绑定手机号的人数）
            $register_num = AccountsInfo::from('AccountsInfo as a')
                ->leftJoin('AgentDB.dbo.agent_relation as b','a.UserID','=','b.user_id')
                ->select(\DB::raw('count(a.UserID) as count_user'))
                ->andFilterBetweenWhere('a.RegisterDate',$start_date,$end_date)
                ->whereRaw(\DB::raw('a.RegisterMobile is not null'))
                ->where('b.parent_user_id',$user_id)//直属推广
                ->first();
            $list['register_num'] = $register_num['count_user'];

            //下级人数（包括游客登录的人数）
            $directly_player_num = AgentRelation::where('parent_user_id',$user_id)
                ->andFilterBetweenWhere('created_at',$start_date,$end_date)
                ->count('user_id');
            $list['directly_player_num'] = $directly_player_num;

            //充值
            $sum_pay_all = PaymentOrder::from('payment_orders as a')
                ->leftJoin('AgentDB.dbo.agent_relation as b','a.user_id','=','b.user_id')
                ->select(\DB::raw('sum(a.amount) as amount'))
                ->where('a.payment_status', PaymentOrder::SUCCESS)
                ->andFilterBetweenWhere('a.success_time',$start_date,$end_date);
            if($game_id){
                $sum_pay_all -> where('a.game_id',$game_id);
            }else{
                $sum_pay_all->where('b.parent_user_id',$user_id);//直属推广
            }
            $sum_pay = $sum_pay_all->first();
            $list['recharge_num'] = $sum_pay['amount']??'0.00';


            $sum_withdrawal_all = AgentWithdrawRecord::from('agent_withdraw_record as a')
                ->leftJoin('AgentDB.dbo.agent_relation as b','a.user_id','=','b.user_id')
                ->select(\DB::raw('sum(a.score) as money'))
                ->where('a.status', AgentWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('a.created_at',$start_date,$end_date);
            if($game_id){
                $sum_withdrawal_all -> where('a.user_id',$s_user['UserID']);
            }else{
                $sum_withdrawal_all -> where('b.parent_user_id',$user_id);//直属推广
            }
            $sum_withdrawal = $sum_withdrawal_all->first();
            $list['withdrawal_num'] = realCoins($sum_withdrawal['money'] ?? 0);

            //投注人数,投注(打码量),流水
            $game_data_all = RecordScoreDaily::from('RecordScoreDaily as a')
                ->leftJoin('AgentDB.dbo.agent_relation as b','a.UserID','=','b.user_id')
                ->select(
                    \DB::raw('count(DISTINCT(a.UserID)) as count_user'),
                    \DB::raw('sum(a.JettonScore) as sum_bet'), //代理推广的下注
                    \DB::raw('sum(a.StreamScore) as sum_water') //代理推广的流水
                )
                ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date);
            if($game_id){
                $game_data_all -> where('a.UserID',$s_user['UserID']);
            }else{
                $game_data_all -> where('b.parent_user_id',$user_id);//直属推广
            }
            $game_data = $game_data_all->first();
            $list['betting_people_num'] = $game_data['count_user'];
            $list['betting_num']        = realCoins($game_data['sum_bet'] ??0);
            $list['stream_score_num']   = realCoins($game_data['sum_water'] ??0);

            //佣金(个人)
            if($game_id)
            {
                $reward_score = AgentIncome::where('user_id',$s_user['UserID'])->andFilterBetweenWhere('start_date',$start_date,$end_date)->sum('reward_score');
            }else{
                $reward_score = AgentIncome::where('user_id',$user_id)->andFilterBetweenWhere('start_date',$start_date,$end_date)->sum('reward_score');
            }
            $list['reward_score'] = realCoins($reward_score ??0);


            $balance = AgentInfo::where('user_id',$user_id)->first();
            $list['balance'] = realCoins($balance['balance'] ??0) ;
            //活动礼金
            $cash_gifts_all =  RecordTreasureSerial::from('RecordTreasureSerial as a')
                ->leftJoin('AgentDB.dbo.agent_relation as b','a.UserID','=','b.user_id')
                ->select(
                    \DB::raw('sum(a.ChangeScore) as sum_score')
                )
                ->andFilterBetweenWhere('a.CollectDate',$start_date,$end_date)
                ->whereIn('TypeID',array_keys(RecordTreasureSerial::getTypes(2)));
            if($game_id){
                $cash_gifts_all -> where('a.UserID',$s_user['UserID']);
            }else{
                $cash_gifts_all -> where('b.parent_user_id',$user_id);//直属推广
            }
            $cash_gifts = $cash_gifts_all->first();
            $list['cash_gifts'] = realCoins($cash_gifts['sum_score'] ??0);

            //首充人数，充值赠送
            $recharge_first_all = FirstRechargeLogs::from('first_recharge_logs as a')
                ->leftJoin('AgentDB.dbo.agent_relation as b','a.user_id','=','b.user_id')
                ->select(
                    \DB::raw('count(DISTINCT(a.user_id)) as count_user'),
                    \DB::raw('sum(a.coins) as coins')
                )
                ->andFilterBetweenWhere('a.created_at',$start_date,$end_date);
            if($game_id){
                $recharge_first_all -> where('a.user_id',$s_user['UserID']);
            }else{
                $recharge_first_all->where('b.parent_user_id',$user_id);//直属推广
            }
            $recharge_first = $recharge_first_all->first();
            $list['recharge_first_people'] = $recharge_first['count_user'];
            $list['recharge_first_num']    = realCoins($recharge_first['coins'] ??0);
            if($game_id){
                $data=[
                    'register_num'          => '/',
                    'directly_player_num'   => '/',
                    'recharge_num'          => $list['recharge_num'],
                    'withdrawal_num'        => $list['withdrawal_num'],
                    'betting_people_num'    => '/',
                    'betting_num'           => $list['betting_num'],
                    'stream_score_num'      => $list['stream_score_num'],
                    'reward_score'          => $list['reward_score'],
                    'cash_gifts'            => $list['cash_gifts'],
                    'recharge_first_people' => '/',
                    'recharge_first_num'    => $list['recharge_first_num'],
                ];
                return ResponeSuccess('请求成功',$data);
            }else{
                return ResponeSuccess('请求成功',$list);
            }
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }

    }
    /**
    * 直属查询
    *
    * */
    public function personalReportForms()
    {
        Validator::make(request()->all(), [
            'user_id'         => ['required','numeric'],
            'game_id'         => ['nullable','numeric'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'user_id.required' => '代理ID必传！',
            'game_id.numeric'  => '代理ID必须是数字，请重新输入！',
        ])->validate();
        try{
            /*字段：ID 充值 活动礼金 充值赠送 投注 下级人数*/
            /*改字段：ID 今日投注 总投注 团队人数 直属人数*/
            $user_id = request('user_id');
            $game_id = request('game_id');
            $user = AccountsInfo::with('agent')->where('UserID',$user_id)->first();
            if (!$user){
                return ResponeFails('用户不存在');
            }
            $s_user['UserID']='';
            if($game_id){
                $s_user = AccountsInfo::where('GameID',$game_id)->first();
                if (!$s_user){
                    return ResponeFails('查询的用户不存在');
                }
                $exit_sub_user = AgentRelation::where('parent_user_id',$user_id)->where('user_id',$s_user['UserID'])->pluck('user_id')->toArray();
                if(!$exit_sub_user){
                    return ResponeFails('该用户不属于直推玩家，请重新输入');
                }
            }
            $start_date = request('start_date',date('Y-m-d',strtotime('-6 days')));
            $end_date   = request('end_date',date('Y-m-d'));
            //查询直属玩家信息
            $list = AgentRelation::from('agent_relation as a')
                ->leftJoin('WHQJAccountsDB.dbo.AccountsInfo as b','a.user_id','=','b.UserID')
                ->select('a.user_id','a.parent_user_id','b.GameID','b.NickName','b.RegisterDate')
                ->andFilterBetweenWhere('b.RegisterDate',$start_date,$end_date)
                ->andFilterWhere('a.user_id',$s_user['UserID'])
                ->where('a.parent_user_id',$user_id)
                ->orderBy('a.created_at','desc')
                ->paginate(20);
            /* $list = AgentRelation::select('user_id','parent_user_id')
                ->with('account:UserID,GameID,NickName')
                ->andFilterWhere('user_id',$s_user['UserID'])
                ->where('parent_user_id',$user_id)
                ->orderBy('created_at','desc')
                ->paginate(20);
             if($end_date){
                $end_date=date('Y-m-d 23:59:59',strtotime($end_date));
            }
            //充值
            $data['recharge_num'] = PaymentOrder::select(['user_id',DB::raw('sum(amount) as amount')])
                ->whereIn('user_id',$list->pluck('user_id')->toArray())
                ->where('payment_status',PaymentOrder::SUCCESS)
                ->andFilterBetweenWhere('success_time',$start_date,$end_date)
                ->groupBy('user_id')
                ->pluck('amount','user_id');
             $data['withdrawal_num'] =  AgentWithdrawRecord::select(['user_id',DB::raw('sum(score) as money')])
                ->whereIn('user_id',$list->pluck('user_id')->toArray())
                ->where('status',AgentWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at',$start_date,$end_date)
                ->groupBy('user_id')
                ->pluck('money','user_id');
             //活动礼金
             $data['cash_gifts'] =  RecordTreasureSerial::select(['UserID',DB::raw('sum(ChangeScore) as ChangeScore')])
                ->whereIn('UserID',$list->pluck('user_id')->toArray())
                ->andFilterBetweenWhere('CollectDate',$start_date,$end_date)
                ->whereIn('TypeID',array_keys(RecordTreasureSerial::getTypes(2)))
                ->groupBy('UserID')
                ->pluck('ChangeScore','UserID');
             //充值赠送（首充）
             $data['recharge_give'] = FirstRechargeLogs::select(['user_id',DB::raw('sum(coins) as coins')])
                ->whereIn('user_id',$list->pluck('user_id')->toArray())
                ->andFilterBetweenWhere('created_at',$start_date,$end_date)
                ->groupBy('user_id')
                ->pluck('coins','user_id');*/
            //总投注
            $data['jetton_score'] =  RecordGameScore::select(['UserID',DB::raw('sum(JettonScore) as JettonScore')])
                ->whereIn('UserID',$list->pluck('user_id')->toArray())
                ->groupBy('UserID')
                ->pluck('JettonScore','UserID');
            //今日投注
            $today= date('Y-m-d');
            $data['today_jetton_score'] =  RecordGameScore::select(['UserID',DB::raw('sum(JettonScore) as JettonScore')])
            ->whereIn('UserID',$list->pluck('user_id')->toArray())
            ->andFilterBetweenWhere('UpdateTime',$today,$today)
            ->groupBy('UserID')
            ->pluck('JettonScore','UserID');
            //直属人数
            $data['directly_player_num'] = AgentRelation::select(['parent_user_id',DB::raw('count(user_id) as num')])
                ->whereIn('parent_user_id',$list->pluck('user_id')->toArray())
                ->groupBy('parent_user_id')
                ->pluck('num','parent_user_id');
            foreach ($list as $k => $v){
                //充值
               /* $list[$k]['recharge_num'] = $data['recharge_num'][$v['user_id']] ?? '0.00';
                $list[$k]['withdrawal_num'] = realCoins($data['withdrawal_num'][$v['user_id']]??0);
                //活动礼金
                $list[$k]['cash_gifts'] = realCoins($data['cash_gifts'][$v['user_id']] ??0);
                //充值赠送
                $list[$k]['recharge_give'] = realCoins($data['recharge_give'][$v['user_id']]??0);*/
                //团队人数
                $list[$k]['team_num'] = AgentRelation::where('rank','like','%,'.$v['user_id'].',%')->count() + 1;
                //投注
                $list[$k]['jetton_score'] = realCoins($data['jetton_score'][$v['user_id']]??0);
                //今日投注
                $list[$k]['today_jetton_score'] = realCoins($data['today_jetton_score'][$v['user_id']]??0);
                //总直属玩家(下级人数)
                $list[$k]['directly_player_num'] = $data['directly_player_num'][$v['user_id']]??0;
                //玩家id
                $list[$k]['game_id'] = $v->account['GameID'] ?? '0';
                //玩家名称
                $list[$k]['nickname'] = $v->account['NickName'] ?? '';
            }
            //总直推人数
            $directly_player = AgentRelation::where('parent_user_id',$user_id)->count();
            //今日总投注
            $JettonScore = RecordGameScore::from('RecordGameScore as a')
                ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                ->select(\DB::raw('sum(a.JettonScore) as JettonScore'))
                ->andFilterBetweenWhere('a.UpdateTime',$today,$today)
                ->where('b.parent_user_id',$user_id)
                ->first();
            $res=[];
            $res['sum_directly_player'] = $directly_player;
            $res['sum_today_jetton_score'] = realCoins($JettonScore['JettonScore']);
            return ResponeSuccessAppend('请求成功',MemberDetailsResource::collection($list),$res);
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }
    /**
     * 我的玩家（暂无此接口）
     *
     */
    public function memberDetails()
    {
        try{
            $user_id = request('user_id');
            $user = AccountsInfo::with('agent')->where('UserID',$user_id)->first();
            if (!$user){
                return ResponeFails('用户不存在');
            }
            //查询直属玩家信息
            $list = AgentRelation::select('user_id','parent_user_id')->with('account:UserID,GameID,NickName')->where('parent_user_id',$user_id)->paginate(30);
            $week_start = Carbon::now()->startOfWeek()->toDateTimeString();
            $week_end = Carbon::now()->endOfWeek()->toDateTimeString();
            //循环统计每个玩家的本周和所有的 团队总人数、直属人数
            foreach ($list as $k => $v){
                //团队人数集合
                $team_ids = AgentRelation::where('rank','like','%,'.$v['user_id'].',%')->pluck('user_id')->toArray();
                //总团队人数
                $list[$k]['sum_team_size'] = strval(count($team_ids));
                //总直属玩家
                $list[$k]['sum_directly_player'] = (string)AgentRelation::where('parent_user_id',$v['user_id'])->count();
                //本周团队人数新增
                $list[$k]['week_team_add'] = (string)AgentRelation::whereIn('user_id',$team_ids)
                    ->andFilterBetweenWhere('created_at',$week_start,$week_end)->count();
                //本周直属玩家新增
                $list[$k]['week_directly_add'] = (string)AgentRelation::where('parent_user_id',$v['user_id'])
                    ->andFilterBetweenWhere('created_at',$week_start,$week_end)->count();
                //玩家id
                $list[$k]['game_id'] = $v->account['GameID'] ?? '0';
                //玩家名称
                $list[$k]['nickname'] = $v->account['NickName'] ?? '';
            }
            return ResponeSuccess('请求成功',MemberDetailsResource::collection($list));
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 我的业绩（暂无此接口）
     *
     */
    public function myBrokerage()
    {
        try{
            $user_id = request('user_id');
            $user = AccountsInfo::with('agent')->where('UserID',$user_id)->first();
            if (!$user){
                return ResponeFails('用户不存在');
            }
            //团队所有人数集合
            $team_ids = AgentRelation::where('rank','like','%,'.$user_id.',%')->pluck('user_id')->toArray();
            //直属团队人数集合
            $zhishu_ids = AgentRelation::where('parent_user_id',$user_id)->pluck('user_id')->toArray();
            $week_start = Carbon::now()->startOfWeek()->toDateTimeString();
            $week_end = Carbon::now()->endOfWeek()->toDateTimeString();
            //本周个人业绩
            $personal_enterprise = RecordScoreDaily::whereIn('UserID',$zhishu_ids)->andFilterBetweenWhere('UpdateDate',$week_start,$week_end)->sum('StreamScore');
            //本周总业绩
            $sum_enterprise = RecordScoreDaily::whereIn('UserID',$team_ids)->andFilterBetweenWhere('UpdateDate',$week_start,$week_end)->sum('StreamScore');
            //本周团队业绩
            $append['team_enterprise'] = realCoins($sum_enterprise - $personal_enterprise);
            $append['personal_enterprise'] = realCoins($personal_enterprise);
            $append['sum_enterprise'] = realCoins($sum_enterprise);
            //=======近期业绩列表（安照日期条件、并以日期分组查询统计）==============//
            //日个人业绩分组统计
            $personal_score = RecordScoreDaily::select('UpdateDate',\DB::raw("sum(StreamScore) as personal_score"))
                ->whereIn('UserID',$zhishu_ids)
                //->andFilterBetweenWhere('UpdateDate',$week_start,$week_end)
                ->groupBy('UpdateDate')->orderBy('UpdateDate','desc')->paginate(10);

            //日总业绩分组统计
            $list     = RecordScoreDaily::select('UpdateDate',\DB::raw("sum(StreamScore) as sum_score"))
                ->whereIn('UserID',$team_ids)
                //->andFilterBetweenWhere('UpdateDate',$week_start,$week_end)
                ->groupBy('UpdateDate')->orderBy('UpdateDate','desc')->paginate(10);

            foreach ($list as $k => $v){
                $list[$k]['personal_score'] = 0;
                foreach ($personal_score as $key => $val){
                    if ($val['UpdateDate'] == $v['UpdateDate']){
                        $list[$k]['personal_score'] = $val['personal_score'];
                        $list[$k]['team_score'] = strval($v['sum_score'] - $val['personal_score']);
                    }
                }
                $list[$k]['team_score'] = strval($v['sum_score'] - $list[$k]['personal_score']);
            }

            return ResponeSuccessAppend('请求成功',MyBrokerageResource::collection($list),$append);
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }

    }
    /**
     * 我的奖励（暂无此接口）
     *
     */
    public function promoteEarnings()
    {
        try{
            $user_id = request('user_id');
            $user = AccountsInfo::with('agent')->where('UserID',$user_id)->first();
            if (!$user){
                return ResponeFails('用户不存在');
            }
            $list = AgentIncome::where('user_id',$user_id)->orderBy('id','desc')->paginate(40);
            return ResponeSuccess('请求成功',AgentIncomeResource::collection($list));
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }

    }

    /**
     *
     *
     */
    public function withdrawDetails()
    {
        try{
            $user_id = request('user_id');
            $user = AccountsInfo::with('agent')->where('UserID',$user_id)->first();
            if (!$user){
                return ResponeFails('用户不存在');
            }
            $list = AgentWithdrawRecord::where('user_id',$user_id)->orderBy('id','desc')->paginate(20);
            $agent = AgentInfo::where('user_id',$user_id)->first();
            $balance = realCoins($agent->balance ?? 0);
            $append['balance'] = strval($balance);
            return ResponeSuccessAppend('请求成功', AgentWithdrawRecordResource::collection($list),$append);
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }

    }
    /**
     *
     *
     */
    public function withdraw(AgentWithdraw $request)
    {
        $databases = ['agent','treasure','record'];
        $db_sql = new AgentInfo;
        $db_sql->beginTransaction($databases);
        try {
            $user_id = request('user_id');
            $user = AccountsInfo::from('AccountsInfo as a')
                ->select('a.GameID','a.UserID','a.CardName','a.BankCardID','a.PhoneNumber','a.BankAddress','b.balance','c.Score','c.InsureScore')
                ->leftJoin(AgentInfo::tableName().' as b','a.UserID','=','b.user_id')
                ->leftJoin(GameScoreInfo::tableName().' as c','a.UserID','=','c.UserID')
                ->where('a.UserID',$user_id)
                ->lockForUpdate()
                ->first();
            if (empty($user->balance) || $user->balance < 10000){
                $db_sql->rollBack($databases);
                return ResponeFails('玩家佣金领取余额不足1');
            }
            $res1 = AgentInfo::where('user_id',$user_id)->update(['balance'=>0]);
            $res2 = GameScoreInfo::where('UserID',$user_id)->increment('Score', $user->balance);
            $res3 = RecordTreasureSerial::addRecord($user_id,$user->Score,$user->InsureScore,$user->balance,RecordTreasureSerial::AGENT_BROKERAGE);
            // $res4 = AgentWithdrawRecord::add($user_id,$user->balance,$user->CardName,$user->PhoneNumber,$user->BankAddress,$user->BankCardID);
            $user->Score += $user->balance;
            if ($res1 && $res2 && $res3){
                $db_sql->commit($databases);
                //通知刷新金币
                giveInform($user_id, $user->Score, $user->balance,1);
                return ResponeSuccess('领取成功');
            }else{
                $db_sql->rollBack($databases);
                return ResponeFails('玩家佣金领取失败');
            }
        }catch (\Exception $e){
            $db_sql->rollBack($databases);
            return ResponeFails('玩家佣金领取失败');
        }
    }
    /**
     *
     *
     */
    /*public function withdraw(AgentWithdraw $request)
    {
        $info = $request->all();
        $info['score'] *= getGoldBase();
        $WithdrawRecord = new AgentWithdrawRecord();
        $db_agent       = \DB::connection('agent');
        if(!$this->checkUserLevelConfig(request('user_id'), UserLevel::PROXY)) {
            return ResponeFails('对方目前VIP等级无法使用该功能');
        }
        try {
            $db_agent->beginTransaction();
            $agent_info = AgentInfo::where('user_id',$info['user_id'])->lockForUpdate()->first();
            if (!$agent_info || $agent_info->balance < $info['score']){
                $db_agent->rollback();
                return ResponeFails('余额不足');
            }
            //不能超过已有额度
            $res  = $WithdrawRecord->add($info);
            $res2 = AgentInfo::where('user_id',$info['user_id'])->decrement('balance', $info['score']);
            if ($res && $res2){
                $db_agent->commit();
                Log::info('客户端'.config('set.withdrawal').'申请',$info);
            }else{
                $db_agent->rollback();
                return ResponeFails(config('set.withdrawal').'失败');
            }

        }catch (\Exception $e) {
            $db_agent->rollback();
            return ResponeFails(config('set.withdrawal').'失败');
        }
        return ResponeSuccess('请求成功',['balance' => strval(realCoins($agent_info->balance - $info['score']))]);
    }*/

    /**
     * 绑定代理(功能不变)
     */
    public function binding()
    {
        $user_id = request('user_id');
        $parent_game_id = request('parent_game_id');
//        if(!$this->checkUserLevelConfig(request('user_id'), UserLevel::PROXY)) {
//            return ResponeFails('对方目前VIP等级无法使用该功能');
//        }
        $parent_user_id = AccountsInfo::query()->where('GameID', $parent_game_id)->value('UserID');
//        if(!$this->checkUserLevelConfig($parent_user_id, UserLevel::PROXY)) {
//            return ResponeFails('对方目前VIP等级无法使用该功能');
//        }
        //查询用户
        $user = AccountsInfo::with(['agent','channel'])->where('UserID',$user_id)->first();
        //查询绑定的代理用户
        $agent_user = AccountsInfo::with(['agent','channel'])->where('GameID',$parent_game_id)->first();
        //判断用户是否存在
        if (!$user){ return ResponeFails('用户不存在'); }
        //判断是否绑定为自己
        if ($user->GameID == $parent_game_id){ return ResponeFails('不能绑定自己'); }
        //判断改用户是否已被绑定
        if ($user->agent['parent_user_id'] != 0){ return ResponeFails('该用户已绑定代理'); }
        //判断绑定的代理是否存在
        if (!$agent_user || !$agent_user->agent){ return ResponeFails('绑定的代理不存在'); }
        //上级不能绑定下级（直属以及所有后代下级）
        $childs = AgentRelation::where('rank','like','%,'.$user_id.',%')->pluck('user_id')->toArray();
        if (in_array($agent_user->UserID,$childs)){ return ResponeFails('上级不能绑定下级'); }
        //自身不能是渠道
        if (!empty($user->channel)){ return ResponeFails('渠道不能绑定代理'); }
        //上级不能是渠道
        //if (!empty($agent_user->channel)){ return ResponeFails('代理不能绑定渠道'); }

        $info['parent_user_id'] = $agent_user->UserID;
        $info['rank'] = $agent_user->agent['rank'] . $agent_user->agent['user_id'] . ',';
        $info['created_at'] = date('Y-m-d H:i:s');
        $db_agent = DB::connection('agent');
        $db_agent->beginTransaction();
        try{
            //更换绑定关系
            $res = AgentRelation::updateOrCreate(['user_id'=>$user_id],$info);
            if (!$res){
                $db_agent->rollBack();
                return ResponeFails('绑定失败');
            }
            //替换掉子集的绑定，加入新的上级
            foreach ($childs as $k => $v){
                $data = AgentRelation::where('user_id',$v)->first();
                $data->rank = $info['rank'].str_replace(',0,','',$data->rank);
                $data->created_at = date('Y-m-d H:i:s');
                //$data->rank = str_replace(','.$user_id.',',','.$agent_user->agent->user_id.','.$user_id.',',$data->rank);
                if (!$data->save()){
                    $db_agent->rollBack();
                    return ResponeFails('绑定失败');
                }
            }
            $db_agent->commit();
            return ResponeSuccess('绑定成功');
        }catch (\Exception $exception){
            $db_agent->rollBack();
            return ResponeFails('绑定失败');
        }

    }

    /**
     * 团队业绩-团队总览
     *
     */
    public function teamOverview($user_id)
    {
        Validator::make(request()->all(), [
            'parent_game_id'  => ['nullable','numeric'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'parent_game_id.numeric'    => '代理ID必须是数字',
            'start_date.date'           => '时间格式有误',
            'end_date.date'             => '时间格式有误',
        ])->validate();
        try{
            //获取查询日期，并设置默认时间为当前七天内的
            $start_date = \request('start_date',Carbon::parse('-7 days')->toDateString());
            $end_date = \request('end_date',Carbon::today()->toDateString());
	        if($end_date){
		        $end_date=date('Y-m-d 23:59:59',strtotime($end_date));
	        }
            $parent_game_id = intval(request('parent_game_id',0));
            if (!empty($parent_game_id)){
                //查询团队的代理
                $user = AccountsInfo::with('agent')->where('IsAndroid',0)->where('GameID',$parent_game_id)->first();
                //判断当前用户是否存在
                if (!$user){
                    return ResponeFails('查询用户不存在');
                }
                //判断查询的是否属于该代理
                if (!in_array($user_id,explode(',',$user->agent->rank))){
                    return ResponeFails('只能查询自己推广的用户');
                }
                //根据查询的game_id来赋值查询
                $user_id = $user->UserID;
            }

            //注册人数
            $bind_num = AccountsInfo::from('AccountsInfo as a')
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.UserID','=','b.user_id')
                ->where('a.RegisterMobile','<>','')//绑定手机号
                ->where('b.rank','like','%'.$user_id.'%')
                ->andFilterBetweenWhere('a.RegisterDate',$start_date,$end_date)
                ->count();
            //团队人数(包含自己)
            $team_num = AgentRelation::where('rank','like','%'.$user_id.'%')->andFilterBetweenWhere('created_at',$start_date,$end_date)->count() + 1;
            //充值
            $pay_score = PaymentOrder::from('payment_orders as a')
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
                ->where('a.payment_status', PaymentOrder::SUCCESS)
                ->where('b.rank','like','%'.$user_id.'%')
                ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
                ->sum('a.coins');

            $withdrawl_score = AgentWithdrawRecord::from('agent_withdraw_record as a')
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
                ->where('a.status', AgentWithdrawRecord::PAY_SUCCESS)
                ->where('b.rank','like','%'.$user_id.'%')
                ->andFilterBetweenWhere('a.updated_at',$start_date,$end_date)
                ->sum('a.score');
            //投注、流水
            $info = RecordScoreDaily::from('RecordScoreDaily as a')
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.UserID','=','b.user_id')
                ->select(
                    \DB::raw('sum(a.JettonScore) as bet_score'),//投注
                    \DB::raw('sum(a.StreamScore) as water_score')//流水
                )
                ->where('b.rank','like','%'.$user_id.'%')
                ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date)
                ->first();
            //充值赠送
            $pay_give = FirstRechargeLogs::from('first_recharge_logs as a')
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
                ->where('b.rank','like','%'.$user_id.'%')
                ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
                ->sum('a.coins');
            //佣金
            $agent = AgentInfo::where('user_id',$user_id)->first();
            //筛选时间内产生的团队佣金总和
            $sum_score = AgentIncome::from('agent_income as a')
                //->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
                //->where('b.rank','like','%'.$user_id.'%')
                ->where('a.user_id',$user_id)
                ->andFilterBetweenWhere('a.start_date',$start_date,$end_date)
                ->sum('a.reward_score');
            //首充人数
            $first_pay = FirstRechargeLogs::from('first_recharge_logs as a')
                ->select(\DB::raw('count(distinct(a.user_id)) as first_pay_num'))
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
                ->where('b.rank','like','%'.$user_id.'%')
                ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
                ->first();
            $first_pay_num = $first_pay['first_pay_num'];
            //投注人数
            $bet = RecordScoreDaily::from('RecordScoreDaily as a')
                ->select(\DB::raw('count(distinct(a.UserID)) as bet_num'))
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.UserID','=','b.user_id')
                ->where('b.rank','like','%'.$user_id.'%')
                ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date)
                ->first();
            $bet_num = $bet['bet_num'];

            //活动礼金
            $active_socre = RecordTreasureSerial::from('RecordTreasureSerial as a')
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.UserID','=','b.user_id')
                ->where('b.rank','like','%'.$user_id.'%')
                ->whereIn('TypeID',array_keys(RecordTreasureSerial::getTypes(2)))
                ->andFilterBetweenWhere('a.CollectDate',$start_date,$end_date)
                ->sum('a.ChangeScore');

            $data = [
                'bind_num'          => $bind_num,//注册人数
                'team_num'          => $team_num,//团队人数(包含自己)
                'pay_score'         => realCoins($pay_score),//充值
                'withdrawl_score'   => realCoins($withdrawl_score),
                'bet_score'         => realCoins($info['bet_score'] ?? 0),//投注
                'water_score'       => realCoins($info['water_score'] ?? 0),//流水
                'bet_num'           => $bet_num,//投注人数
                'pay_give'          => realCoins($pay_give),//充值赠送
                'brokerage'         => realCoins($agent['balance'] ?? 0),//佣金
                'sum_score'         => realCoins($sum_score ?? 0),//总佣金
                'first_pay_num'     => $first_pay_num,//首充人数
                'active_socre'      => realCoins($active_socre),//活动礼金
            ];
            return ResponeSuccess('请求成功',$data);
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }

    }

    /**
     * 团队业绩-团队报表
     *
     */
    public function teamReportForms($user_id)
    {
        Validator::make(request()->all(), [
            'parent_game_id'  => ['nullable','numeric'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'parent_game_id.numeric'    => '代理ID必须是数字',
            'start_date.date'           => '时间格式有误',
            'end_date.date'             => '时间格式有误',
        ])->validate();
        try{
            //获取查询日期，并设置默认时间为当前七天内的
            $start_date = \request('start_date','1970-01-01');
            $end_date = \request('end_date',Carbon::today()->toDateString());
	        if($end_date){
		        $end_date=date('Y-m-d 23:59:59',strtotime($end_date));
	        }
            $parent_game_id = intval(request('parent_game_id',0));
            if (!empty($parent_game_id)){
                //查询团队的代理
                $user = AccountsInfo::with('agent')->where('IsAndroid',0)->where('GameID',$parent_game_id)->first();
                //判断当前用户是否存在
                if (!$user){
                    return ResponeFails('查询用户不存在');
                }
                //判断查询的是否属于该代理
                if (!in_array($user_id,explode(',',$user->agent->rank))){
                    return ResponeFails('只能查询自己推广的用户');
                }
                //根据查询的game_id来赋值查询
                $user_id = $user->UserID;
            }

            // 按天
            $list = AgentIncome::where('user_id',$user_id)
                ->select('user_id','person_score',\DB::raw('(person_score+team_score) AS team_score'),'reward_score','start_date','end_date','created_at','directly_new','team_new')
                ->whereBetween('start_date', [$start_date, $end_date])
                ->orderBy('start_date','DESC')
                ->paginate(10);
            return ResponeSuccess('请求成功',TeamReportFormsResource::collection($list));
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 团队业绩-业绩来源
     * 产品lls：直属来源 2020年5月28日10:32:18
     */
    public function teamReportDetail($user_id)
    {
        Validator::make(request()->all(), [
            'start_date'      => ['date'],
        ], [
            'start_date.date'           => '时间格式有误',
        ])->validate();
        try{
            //获取查询日期，并设置默认时间为当前七天内的
            $start_date = \request('start_date',date('Y-m-d'));
            $end_date = date('Y-m-d 23:59:59',strtotime($start_date));
            // 按玩家
            $list = \DB::table(AgentIncomeDetails::tableName()." as a")
                ->leftJoin(AgentIncome::tableName().' as b', function($join)use($start_date,$end_date){
                    $join->on('a.user_id','=','b.user_id')
                        ->whereBetween('b.start_date', [$start_date,$end_date]);
                })
                ->leftJoin(AccountsInfo::tableName().' as c','a.user_id','=','c.UserID')
                ->where('a.parent_user_id',$user_id)
                ->whereBetween('a.create_date', [$start_date,$end_date])
                ->select(
                    'c.GameID as user_id',
                    \DB::raw('min(a.create_date) as start_date'),
                    \DB::raw('min(b.directly_new) as directly_new'),
                    \DB::raw('min(b.team_new) as team_new'),
                    \DB::raw('sum(a.direct_score) as person_score'),
                    \DB::raw('sum(a.direct_score+a.team_score) as team_score'),
                    \DB::raw('sum(a.money) as reward_score')
                )
                ->groupBy('c.GameID')
                ->orderBy('person_score','DESC')
                ->paginate(10);
            return ResponeSuccess('请求成功',TeamReportFormsResource::collection($list));
        }catch (\Exception $exception){ \Log::info($exception);
            return ResponeFails('异常错误');
        }

    }

    /**
     * 推广红包-充值记录
     *
     */
    public function payList($user_id)
    {
        try{
            $list = PaymentOrder::from('payment_orders as a')
                ->select('a.user_id',DB::raw('sum(a.amount) as amounts'),DB::raw('min(a.created_at) as created_at'),'c.NickName')
                ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
                ->leftJoin(AccountsInfo::tableName().' as c','a.user_id','=','c.UserID')
                ->where('a.payment_status', PaymentOrder::SUCCESS)
                ->where('b.parent_user_id',$user_id)
                ->groupBy('a.user_id','c.NickName')
                ->havingRaw('sum(a.amount) >= 100')
                ->paginate(10);

            foreach ($list as $key => $val){
                $order = PaymentOrder::select('amount','created_at')->where('user_id',$val->user_id)
                    ->where('payment_status',PaymentOrder::SUCCESS)
                    ->orderBy('id','asc')->limit(20)->get();
                $sum = 0;
                foreach ($order as $k => $v){
                    $sum += $v->amount;
                    if ($sum >= 100){
                        $list[$key]['created_at'] = $v->created_at;
                        break;
                    }
                }
            }

            return ResponeSuccess('请求成功',PayListResource::collection($list));
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }

    }
}
