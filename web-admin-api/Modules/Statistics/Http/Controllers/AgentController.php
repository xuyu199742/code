<?php

namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Agent\AgentIncomeDetails;
use Models\Agent\AgentWithdrawRecord;
use function foo\func;
use Carbon\Carbon;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentIncome;
use Models\Agent\AgentInfo;
use Models\Agent\AgentRelation;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;
use Transformers\AgentBalanceReportDetailsTransformer;
use Transformers\AgentBalanceReportDetails;
use Transformers\AgentBalanceReportTransformer;
use Transformers\AgentReportTransformer;
use Transformers\AgentIncomeDetailTransformer;

class AgentController extends Controller
{
    /**
     * 代理报表
     *
     */
    public function agentReport()
    {
        \Validator::make(request()->all(), [
            'game_id'   => 'nullable|numeric',
            'parent_game_id'   => 'nullable|numeric',
            'start_date'=> 'nullable|date',
            'end_date'  => 'nullable|date',
            'type'      => 'in:1,2',
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'parent_game_id.numeric' => '父级代理ID必须数字',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'type.in'   => '类型不不在可选范围内',
        ])->validate();
        $start_date = request('start_date');
        $end_date   = request('end_date');
        $game_id    = request('game_id');
        $parent_game_id    = request('parent_game_id');
        $type       = request('type',1);
        if (!$parent_game_id){
            \Validator::make(request()->all(), [
                'start_date'=> 'required',
                'end_date'  => 'required',
            ], [
                'start_date.required' => '开始日期不能为空',
                'end_date.required'   => '结束日期不能为空',
            ])->validate();
        }
        try{
            //dumpSql();
            $with = ['subAgent','agentinfo','incomes','payment'=>function($query){
                $query->where('payment_status',PaymentOrder::SUCCESS);
            },'withdraw'=>function($query){
                $query->where('status',WithdrawalOrder::PAY_SUCCESS);
            }];
            $joinSql1 = " as a1 left join ".AgentRelation::tableName()." as b1 on a1.user_id=b1.user_id ";
            $joinSql2 = " as a1 left join ".AgentRelation::tableName()." as b1 on a1.UserID=b1.user_id ";
            //$whereTeam = " where (b1.rank like '%'+CAST(a.user_id as varchar)+'%' or b1.user_id=a.user_id)";
            $whereTeam = " where ((b1.rank like '%'+CAST(a.user_id as varchar)+'%') or b1.user_id=a.user_id)";
            $searchDateSql1 = $this->searchDate($start_date,$end_date,'a1.created_at');
            $searchDateSql2 = $this->searchDate($start_date,$end_date,'a1.UpdateDate');
            $searchDateSql3 = $this->searchDate($start_date,$end_date,'a1.CollectDate');//RecordTreasureSerial
            $searchDateSql4 = $this->searchDate($start_date,$end_date,'a1.UpdateTime');//RecordGameScore
            $searchDateSql5 = $this->searchDate($start_date,$end_date,'a1.CreateDate');//RecordUserLogon
            $whereStr = !empty($end_date) ? " and created_at <= '".$end_date." 23:59:59'" : '';
            if ($type == 1){
                //========直推=========
                $field = [
                    'a.*','b.GameID as game_id','c.GameID as parent_game_id',
                    //直推用户流水
                    //\DB::raw("(select sum(StreamScore) from ".RecordScoreDaily::tableName().$joinSql2." where b1.parent_user_id=a.user_id".$searchDateSql2.") as sub_water"),
                    //直推账户余额
                    //\DB::raw("(select sum(Score) from ".GameScoreInfo::tableName().$joinSql2." where b1.parent_user_id=a.user_id) as sub_balance"),
                    //直推中奖
                    //\DB::raw("(select sum(RewardScore) from ".RecordScoreDaily::tableName().$joinSql2." where b1.parent_user_id=a.user_id".$searchDateSql2.") as SubWinnerPaid"),
                    //直推盈利
                    \DB::raw("(select sum(ChangeScore) from ".RecordScoreDaily::tableName().$joinSql2." where b1.parent_user_id=a.user_id".$searchDateSql2.") as sub_winlose"),
                    //直推人数
                    \DB::raw("(select count(*) from ".AgentRelation::tableName()." where parent_user_id=a.user_id)as sub_people"),
                    //直推充值人数
                    \DB::raw("(select count(distinct(a1.user_id)) from ".PaymentOrder::tableName().$joinSql1." where b1.parent_user_id=a.user_id and a1.payment_status='".PaymentOrder::SUCCESS."'".$searchDateSql1.") as sub_pay_people"),
                    //直推充值金额
                    \DB::raw("(select sum(amount) from ".PaymentOrder::tableName().$joinSql1." where b1.parent_user_id=a.user_id and a1.payment_status='".PaymentOrder::SUCCESS."'".$searchDateSql1.") as sub_pay_money"),
                    //直推提现人数
                    \DB::raw("(select count(distinct(a1.user_id)) from ".WithdrawalOrder::tableName().$joinSql1." where b1.parent_user_id=a.user_id and a1.status='".WithdrawalOrder::PAY_SUCCESS."'".$searchDateSql1.") as sub_withdrawal_people"),
                    //直推提现金额
                    \DB::raw("(select sum(money) from ".WithdrawalOrder::tableName().$joinSql1." where b1.parent_user_id=a.user_id and a1.status='".WithdrawalOrder::PAY_SUCCESS."'".$searchDateSql1.") as sub_withdrawal_money"),
                    //直推投注人数
                    \DB::raw("(select count(distinct(a1.UserID)) from ".RecordScoreDaily::tableName().$joinSql2." where b1.parent_user_id=a.user_id".$searchDateSql2.") as sub_bet_people"),
                    //直推投注金额
                    \DB::raw("(select sum(JettonScore) from ".RecordScoreDaily::tableName().$joinSql2." where b1.parent_user_id=a.user_id".$searchDateSql2.") as sub_bet_money")

                ];
            }else{
                //======团队======
                $field = [
                    'a.*','b.GameID as game_id','c.GameID as parent_game_id',
                    //团队审核打码量
                    //\DB::raw("(select sum(CurJettonScore) from ".GameScoreInfo::tableName().$joinSql2.$whereTeam.") as CurJettonScore"),
                    //团队中奖金额
                    //\DB::raw("(select sum(RewardScore) from ".RecordScoreDaily::tableName().$joinSql2.$whereTeam.$searchDateSql2.") as WinnerPaid"),
                    //团队用户流水
                    //\DB::raw("(select sum(StreamScore) from ".RecordScoreDaily::tableName().$joinSql2.$whereTeam.$searchDateSql2.") as team_water"),
                    //团队账户余额
                    //\DB::raw("(select sum(Score) from ".GameScoreInfo::tableName().$joinSql2.$whereTeam.") as team_balance"),
                    //团队注册人数
                    //\DB::raw("(select count(*) from ".AccountsInfo::tableName()." as a1 right join ".AgentRelation::tableName()." as b1 on a1.UserID=b1.user_id ".$whereTeam." and a1.RegisterMobile is not null ".$this->searchDate($start_date,$end_date,'a1.RegisterDate').") as team_reg_people"),
                    //团队新增人数
                    \DB::raw("(select count(*) from ".AgentRelation::tableName()." where rank like '%'+CAST(a.user_id as varchar)+'%' ".$this->searchDate($start_date,$end_date,'created_at').") as team_add_people"),
                    //团队截止时间的总人数
                    \DB::raw("(select count(*) from ".AgentRelation::tableName()." where rank like '%'+CAST(a.user_id as varchar)+'%' or user_id=a.user_id ".$whereStr." ) as team_sum_people"),
                    //团队充值人数
                    \DB::raw("(select count(distinct(a1.user_id)) from ".PaymentOrder::tableName().$joinSql1.$whereTeam." and a1.payment_status='".PaymentOrder::SUCCESS."'".$searchDateSql1.") as team_pay_people"),
                    //团队充值金额
                    \DB::raw("(select sum(amount) from ".PaymentOrder::tableName().$joinSql1.$whereTeam." and a1.payment_status='".PaymentOrder::SUCCESS."'".$searchDateSql1.") as team_pay_money"),
                    //团队提现人数
                    \DB::raw("(select count(distinct(a1.user_id)) from ".WithdrawalOrder::tableName().$joinSql1.$whereTeam." and a1.status='".WithdrawalOrder::PAY_SUCCESS."'".$searchDateSql1.") as team_withdrawal_people"),
                    //团队提现金额
                    \DB::raw("(select sum(money) from ".WithdrawalOrder::tableName().$joinSql1.$whereTeam." and a1.status='".WithdrawalOrder::PAY_SUCCESS."'".$searchDateSql1.") as team_withdrawal_money"),
                    //团队投注人数
                    \DB::raw("(select count(distinct(a1.UserID)) from ".RecordScoreDaily::tableName().$joinSql2.$whereTeam.$searchDateSql2.") as team_bet_people"),
                    //团队投注金额
                    \DB::raw("(select sum(JettonScore) from ".RecordScoreDaily::tableName().$joinSql2.$whereTeam.$searchDateSql2.") as team_bet_money"),
                    //团队用户输赢修改为：团队玩家输赢=中奖-投注 RewardScore-JettonScore = ChangeScore
                    \DB::raw("(select sum(ChangeScore) from ".RecordScoreDaily::tableName().$joinSql2.$whereTeam.$searchDateSql2.") as team_winlose"),
                    //团队活动礼金
                    \DB::raw("(select sum(ChangeScore) from ".RecordTreasureSerial::tableName().$joinSql2.$whereTeam.$searchDateSql3." and TypeID in(".implode(',',array_keys(RecordTreasureSerial::getTypes(2))).")) as active_score"),
                    //团队日活跃人数
                    \DB::raw("(select count(distinct(a1.UserID)) from ".RecordUserLogon::tableName().$joinSql2.$whereTeam.$searchDateSql5.") as active_people")

                ];
            }
            $joinSub = AgentRelation::select('parent_user_id')->where('parent_user_id','>',0)->groupBy('parent_user_id');
            $agent_list = AgentRelation::from('agent_relation as a')
                ->select($field)
                ->with($with)
                ->when(!$parent_game_id,function ($query)use($joinSub){
                    $query->joinSub($joinSub,'j','a.user_id','=','j.parent_user_id');
                })
                ->leftJoin(AccountsInfo::tableName().' as b','a.user_id','=','b.UserID')
                ->leftJoin(AccountsInfo::tableName().' as c','a.parent_user_id','=','c.UserID')
                ->andFilterWhere('b.GameID',$game_id)
                ->andFilterWhere('c.GameID',$parent_game_id)
                ->orderBy('a.created_at','desc')->paginate(10);
            $list = $this->forData($agent_list,$type,$start_date,$end_date);
            return $this->response->paginator($list, new AgentReportTransformer());
        }catch (\Exception $exception){
            return ResponeFails('异常错误:'.$exception->getMessage());
        }
    }

    //代理总计
    public function agentTotal(){
        \Validator::make(request()->all(), [
            'game_id'   => 'nullable|numeric',
            'parent_game_id'   => 'nullable|numeric',
            'start_date'=> 'nullable|date',
            'end_date'  => 'nullable|date',
            'type'      => 'in:1,2',
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'parent_game_id.numeric' => '父级代理ID必须数字',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
            'type.in'   => '类型不不在可选范围内',
        ])->validate();
        $start_date = request('start_date');
        $end_date   = request('end_date');
        $game_id    = request('game_id');
        $parent_game_id    = request('parent_game_id');
        $type       = request('type',1);
        if (!$parent_game_id){
            \Validator::make(request()->all(), [
                'start_date'=> 'required',
                'end_date'  => 'required',
            ], [
                'start_date.required' => '开始日期不能为空',
                'end_date.required'   => '结束日期不能为空',
            ])->validate();
        }
        try{
            //dumpSql();
            $user_id = null;
            if ($game_id){
                $Account = AccountsInfo::where('GameID',$game_id)->first();
                if ($Account){
                    $user_id = $Account->UserID;
                }else{
                    return ResponeFails('该用户不存在！');
                }
            }

            if ($type == 1){
                //下级代理人数
                $data['sub_people_num_total'] = AgentRelation::where('parent_user_id','>',0)
                    ->andFilterWhere('parent_user_id',$user_id)
                    ->count();
                //直推新增
                $data['sub_people_add_num_total'] = AgentRelation::where('parent_user_id','>',0)
                    ->andFilterWhere('parent_user_id',$user_id)
                    ->andFilterBetweenWhere('created_at',$start_date,$end_date)
                    ->count();
                //直推首充人数/金额
                $firstPay = $this->firstPay($start_date,$end_date,$user_id);
                //直推充值人数
                $data['sub_pay_people_total'] = PaymentOrder::from('payment_orders as a')
                    ->select(\DB::raw("count(distinct(a.user_id)) as num"))
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->where('a.payment_status',PaymentOrder::SUCCESS)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
                    ->first()->num;
                //直推充值金额
                $sub_pay_money_total = PaymentOrder::from('payment_orders as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->where('a.payment_status',PaymentOrder::SUCCESS)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
                    ->sum('a.coins');
                //直推提现人数
                $data['sub_withdrawal_people_total'] = WithdrawalOrder::from('withdrawal_orders as a')
                    ->select(\DB::raw("count(distinct(a.user_id)) as num"))
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->where('a.status',WithdrawalOrder::PAY_SUCCESS)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
                    ->first()->num;
                //直推提现金额
                $sub_withdrawal_money_total = WithdrawalOrder::from('withdrawal_orders as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->where('a.status',WithdrawalOrder::PAY_SUCCESS)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date)
                    ->sum('a.real_gold_coins');
                //直推投注人数
                $data['sub_bet_people_total'] = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->select(\DB::raw("count(distinct(a.UserID)) as num"))
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date)
                    ->first()->num;
                //直推投注金额
                $sub_bet_money_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date)
                    ->sum('a.JettonScore');
                //直推用户流水
                $sub_water_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date)
                    ->sum('a.StreamScore');
                //直推账户余额
                $sub_balance_total = GameScoreInfo::from('GameScoreInfo as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->sum('a.Score');
                //直推用户盈利
                $sub_winlose_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->where('b.parent_user_id','>',0)
                    ->andFilterWhere('b.parent_user_id',$user_id)
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date)
                    ->sum('a.ChangeScore');
                //可提余额
                $tx_balance_total = AgentInfo::where('user_id',$user_id)->first();

                //佣金
                //$data['brokerage_total'] = 1;
                //团队打码量
                //$data['CurJettonScore_total'] = 1;
                $data['first_pay_people_total'] = $firstPay->num;
                $data['first_pay_money_total'] = realCoins($firstPay->coins);
                $data['sub_pay_money_total'] = realCoins($sub_pay_money_total);
                $data['sub_withdrawal_money_total'] = realCoins($sub_withdrawal_money_total);
                $data['sub_bet_money_total'] = realCoins($sub_bet_money_total);
                $data['sub_water_total'] = realCoins($sub_water_total);
                $data['sub_balance_total'] = realCoins($sub_balance_total);
                $data['sub_winlose_total'] = realCoins($sub_winlose_total);
                $data['tx_balance_total'] = realCoins($tx_balance_total->balance ?? 0);


            }else{
                //团队新增人数
                $team_add_people_total = AgentRelation::from('agent_relation as b')
                    ->andFilterBetweenWhere('b.created_at',$start_date,$end_date);
                $data['team_add_people_total'] = $this->teamWhere($team_add_people_total,false,$user_id,false);
                //团队充值人数
                $team_pay_people_total = PaymentOrder::from('payment_orders as a')
                    ->select(\DB::raw("count(distinct(a.user_id)) as num"))
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('a.payment_status',PaymentOrder::SUCCESS)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date);
                $data['team_pay_people_total'] = $this->teamWhere($team_pay_people_total,false,$user_id,true,true)->num;
                //团队充值金额
                $team_pay_money_total = PaymentOrder::from('payment_orders as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('a.payment_status',PaymentOrder::SUCCESS)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date);
                $team_pay_money_total = $this->teamWhere($team_pay_money_total,'a.coins',$user_id);
                //团队提现人数
                $team_withdrawal_people_total = WithdrawalOrder::from('withdrawal_orders as a')
                    ->select(\DB::raw("count(distinct(a.user_id)) as num"))
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('a.status',WithdrawalOrder::PAY_SUCCESS)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date);
                $data['team_withdrawal_people_total'] = $this->teamWhere($team_withdrawal_people_total,false,$user_id,true,true)->num;
                //团队提现金额
                $team_withdrawal_money_total = WithdrawalOrder::from('withdrawal_orders as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->where('a.status',WithdrawalOrder::PAY_SUCCESS)
                    ->andFilterBetweenWhere('a.created_at',$start_date,$end_date);
                $team_withdrawal_money_total = $this->teamWhere($team_withdrawal_money_total,'a.real_gold_coins',$user_id);
                //团队有效投注人数
                $team_bet_people_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->select(\DB::raw("count(distinct(a.UserID)) as num"))
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date);
                $data['team_bet_people_total'] = $this->teamWhere($team_bet_people_total,false,$user_id,true,true)->num;
                //团队有效投注金额
                $team_bet_money_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date);
                $team_bet_money_total = $this->teamWhere($team_bet_money_total,'a.JettonScore',$user_id);
                //团队用户流水
                /*$team_water_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date);
                $team_water_total = $this->teamWhere($team_water_total,'a.StreamScore',$user_id);*/
                //团队账户余额
               /* $team_balance_total = GameScoreInfo::from('GameScoreInfo as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id');
                $team_balance_total = $this->teamWhere($team_balance_total,'a.Score',$user_id);*/
                //团队玩家输赢 = 中奖 - 投注
                $team_winlose_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date);
                $team_winlose_total = $this->teamWhere($team_winlose_total,\DB::raw("a.ChangeScore"),$user_id);
                //团队佣金
                /*$brokerage = AgentIncome::from('agent_income as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
                    ->andFilterBetweenWhere('a.start_date',$start_date,$end_date);
                $brokerage = $this->teamWhere($brokerage,'a.reward_score',$user_id);*/
                //团队利润 = 充值 - 提现 - 佣金
                //$data['profit_total'] = realCoins($team_pay_money_total - $team_withdrawal_money_total - $brokerage);

                //团队中奖金额
                /*$WinnerPaid_total = RecordGameScore::from('RecordGameScore as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->andFilterBetweenWhere('a.UpdateTime',$start_date,$end_date);
                $WinnerPaid_total = $this->teamWhere($WinnerPaid_total,'a.RewardScore',$user_id);*/
                //团队活动礼金
                $active_score_total = RecordTreasureSerial::from('RecordTreasureSerial as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->whereIn('a.TypeID',array_keys(RecordTreasureSerial::getTypes(2)))
                    ->andFilterBetweenWhere('a.CollectDate',$start_date,$end_date);
                $active_score_total = $this->teamWhere($active_score_total,'a.ChangeScore',$user_id);
                //团队投注金额
                $CurJettonScore_total = RecordScoreDaily::from('RecordScoreDaily as a')
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->andFilterBetweenWhere('a.UpdateDate',$start_date,$end_date);
                $CurJettonScore_total = $this->teamWhere($CurJettonScore_total,'a.JettonScore',$user_id);
                //团队日活跃人数
                $active_people_total = RecordUserLogon::from('RecordUserLogon as a')
                    ->select(\DB::raw("count(distinct(a.UserID)) as num"))
                    ->leftJoin(AgentRelation::tableName().' as b','a.UserID','=','b.user_id')
                    ->andFilterBetweenWhere('a.CreateDate',$start_date,$end_date);
                $data['active_people_total'] = $this->teamWhere($active_people_total,false,$user_id,true,true)->num;

                //团队注册人数
                //$data['team_reg_people_total'] = 1;

                $data['team_pay_money_total'] = realCoins($team_pay_money_total);
                $data['team_withdrawal_money_total'] = realCoins($team_withdrawal_money_total);
                $data['team_bet_money_total'] = realCoins($team_bet_money_total);
                //$data['team_water_total'] = realCoins($team_water_total);
                //$data['team_balance_total'] = realCoins($team_balance_total);
                $data['team_winlose_total'] = realCoins($team_winlose_total);
                //$data['WinnerPaid_total'] = realCoins($WinnerPaid_total);
                $data['active_score_total'] = realCoins($active_score_total);
                $data['CurJettonScore_total'] = realCoins($CurJettonScore_total);
            }
            return ResponeSuccess('请求成功',$data);
        }catch (\Exception $exception){
            return ResponeFails('异常错误:'.$exception->getMessage());
        }
    }


    private function teamWhere($obj,$field = false,$user_id = null,$is_me = true,$is_repeat = false)
    {
        if ($user_id){
            $obj = $obj->where(function ($query)use ($user_id,$is_me){
                if ($is_me === false){
                    $query->where('b.rank','like','%,'.$user_id.',%');
                }else{
                    $query->where('b.rank','like','%,'.$user_id.',%')->orWhere('b.user_id',$user_id);
                }
            });
        }
        if (!$user_id && $is_me != false){
            $obj->where('b.parent_user_id','>',0);
        }
        if ($field){
            $data = $obj->sum($field);
        }elseif ($is_repeat){
            $data = $obj->first();
        }else{
            $data = $obj->count();
        }
        return $data;
    }

    public function forData($list,$type,$start_date,$end_date){
        foreach ($list as $k => $v){
            if ($type == 1){
                //可提现余额
                $list[$k]['tx_balance'] = $v->agentinfo->balance ?? 0;
                //时间条件有无统计数据不同
                if (empty($start_date) && empty($end_date)){
                    //直属下级新增人数
                    $list[$k]['sub_people_add_num'] = '/';
                    //佣金
                    $list[$k]['brokerage'] = $v->agentinfo->balance ?? 0;
                }else{
                    //直属下级新增人数
                    $list[$k]['sub_people_add_num'] = $v->subAgent()->andFilterBetweenWhere('created_at',$start_date,$end_date)->count();
                    //佣金
                    $list[$k]['brokerage'] = $v->incomes()->andFilterBetweenWhere('start_date',$start_date,$end_date)->sum('reward_score');
                }
                //直推首充金额
                $firstPay = $this->firstPay($start_date,$end_date,$v->user_id);
                $list[$k]['first_pay_money'] = $firstPay->coins;
                //直推首充人数
                $list[$k]['first_pay_people'] = $firstPay->num;
            }else{
                //注册付费率
                /*if (!empty($v['team_reg_people']) && !empty($v['team_pay_people'])){
                    $list[$k]['reg_rate'] = number_format($v['team_pay_people'] * 100 / $v['team_reg_people'],'2');
                }else{
                    $list[$k]['reg_rate'] = '0.00';
                }*/
                //APRU人均付费率
                /*if ($v['team_sum_people']){
                    $list[$k]['pay_rate'] = number_format($v['team_pay_money'] / $v['team_sum_people'],'2');
                }else{
                    $list[$k]['pay_rate'] = '0.00';
                }*/
                //利润
                /*$payment = $v->payment->sum('coins') ?? 0;
                $withdraw = $v->withdraw->sum('real_gold_coins') ?? 0;
                $brokerage = $v->incomes->sum('reward_score') ?? 0;
                $list[$k]['profit'] = $payment - $withdraw - $brokerage;*/
            }
        }
        return $list;
    }

    /**
     * 代理佣金报表
     *
     */
    public function agentBalanceReport()
    {
        \Validator::make(request()->all(), [
            'game_id'    => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ], [
            'game_id.integer'       => '代理id必须是数字',
            'start_date.date'       => '无效日期',
            'end_date.date'         => '无效日期',
        ])->validate();
        try{
            $time = [request('start_date', '1970-01-01'), request('end_date',  now()->format('Y-m-d'))];
            $list = AgentRelation::from('agent_relation as a')
                ->select(
                    \DB::raw("(select sum(reward_score) from ".AgentIncome::tableName()." where user_id=a.user_id and start_date between '".$time[0]."' and '".$time[1]."') as reward_score"),
                    \DB::raw("(select sum(person_score) from ".AgentIncome::tableName()." where user_id=a.user_id and start_date between '".$time[0]."' and '".$time[1]."') as person_score"),
                    \DB::raw("ISNULL((select sum(ChangeScore) from ".RecordTreasureSerial::tableName()." where UserID=a.user_id and TypeID=50 and CollectDate between '".$time[0]." 00:00:00' and '".$time[1]." 23:59:59'), 0) as agent_score"),
                    'a.user_id','a.created_at','b.GameID'
                )
                ->leftJoin(AccountsInfo::tableName().' as b','a.user_id','=','b.UserID')
//                ->andFilterBetweenWhere('a.created_at', request('start_date'), request('end_date'))
                ->andFilterWhere('b.GameID',request('game_id'))
                ->orderByRaw("(select sum(person_score) from ".AgentIncome::tableName()." where user_id=a.user_id and start_date between '".$time[0]."' and '".$time[1]."') desc");
            $total = clone $list;
            $list = $list->paginate(config('page.list_rows'));
            $total = $total->get()->toArray();
            $total_data = [
                'reward_score_total' => realCoins(twin_sum($total,'reward_score')),
                'person_score_total' => realCoins(twin_sum($total,'person_score')),
                'agent_score_total' => realCoins(twin_sum($total,'agent_score')),
            ];
            return $this->response->paginator($list, new AgentBalanceReportTransformer())->addMeta('total',$total_data);
        }catch (\Exception $exception){
            info($exception);
            return ResponeFails('非法操作');
        }
    }

    /**
     * 代理佣金报表详情(废弃)
     *
     */
//    public function agentBalanceReportDetails()
//    {
//        \Validator::make(request()->all(), [
//            'user_id'    => 'required|integer',
//            'game_id'    => 'nullable|integer',
//        ], [
//            'user_id.required'      => '代理标识必传',
//            'user_id.integer'       => '代理标识必须是数字',
//            'game_id.integer'       => '代理id必须是数字',
//        ])->validate();
//        try{
//            $start_date = date('Y-m-d 00:00:00');
//            $end_date   = date('Y-m-d 23:59:59');
//            $user_id    = request('user_id');
//            $list = AgentRelation::from('agent_relation as a')
//                ->select('a.user_id','b.GameID as game_id',
//                    \DB::raw("(select sum(amount) from ".PaymentOrder::tableName()." where user_id=a.user_id and payment_status='".PaymentOrder::SUCCESS."') as sum_pay"),
//                    \DB::raw("(select sum(amount) from ".PaymentOrder::tableName()." where user_id=a.user_id ".$this->searchDate($start_date,$end_date,'created_at')." and payment_status='".PaymentOrder::SUCCESS."') as today_pay"),
//                    \DB::raw("(select sum(money) from ".WithdrawalOrder::tableName()." where user_id=a.user_id and status='".WithdrawalOrder::PAY_SUCCESS."') as sum_withdrawal"),
//                    \DB::raw("(select sum(money) from ".WithdrawalOrder::tableName()." where user_id=a.user_id ".$this->searchDate($start_date,$end_date,'created_at')." and status='".WithdrawalOrder::PAY_SUCCESS."') as today_withdrawal"),
//                    \DB::raw("sum(c.RewardScore-c.JettonScore) as winlose"), //输赢修改为：玩家输赢=中奖-投注
//                    \DB::raw("sum(c.JettonScore) as bet"),
//                    \DB::raw("sum(c.StreamScore) as water"),
//                    \DB::raw("sum(c.SystemServiceScore) as revenue")
//                )
//                ->leftJoin(AccountsInfo::tableName().' as b','a.user_id','=','b.UserID')
//                ->leftJoin(RecordScoreDaily::tableName().' as c','a.user_id','=','c.UserID')
//                ->where('a.rank','like','%'.$user_id.'%')
//                ->andFilterWhere('b.GameID',request('game_id'))
//                ->groupBy('a.user_id','b.GameID')
//                ->paginate(config('page.list_rows'));
//            return $this->response->paginator($list, new AgentBalanceReportDetailsTransformer());
//        }catch (\Exception $exception){
//            return ResponeFails('异常错误');
//        }
//    }
    /**
     * 代理佣金报表详情（新）（客户端：团队业绩-团队报表）
     *
     */
    public function agentBalanceReportDetails()
    {
        \Validator::make(request()->all(), [
            'user_id'    => 'required|integer',
            'game_id'    => 'nullable|integer',
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'user_id.required'      => '代理标识必传',
            'user_id.integer'       => '代理标识必须是数字',
            'game_id.integer'       => '代理id必须是数字',
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
            $user_id = intval(request('user_id',0));
            $parent_game_id = intval(request('game_id',0));
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
            $list = AgentIncome::where('user_id',$user_id)->whereBetween('start_date', [$start_date, $end_date])->orderBy('start_date','DESC')->paginate(config('page.list_rows'));
            return $this->response->paginator($list, new AgentBalanceReportDetails());
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 代理佣金报表详情——业绩来源（新）团队业绩-业绩来源
     * 产品lls：直属来源 2020年5月28日10:32:18
     */
    public function teamReportDetail()
    {
        \Validator::make(request()->all(), [
            'user_id'    => 'required|integer',
            'start_date'      => ['date'],
        ], [
            'user_id.required'      => '代理标识必传',
            'user_id.integer'       => '代理标识必须是数字',
            'start_date.date'           => '时间格式有误',
        ])->validate();
        try{
            //获取查询日期，并设置默认时间为当前七天内的
            $start_date = \request('start_date',date('Y-m-d',strtotime('+7 days')));
            $end_date = \request('start_date',Carbon::today()->toDateString());
            if($end_date){
                $end_date=date('Y-m-d 23:59:59',strtotime($end_date));
            }
            $user_id = intval(request('user_id',0));
            // 按玩家
            $list = AgentIncomeDetails::from(AgentIncomeDetails::tableName()." as a")
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
                ->paginate(config('page.list_rows'));
            return $this->response->paginator($list, new AgentIncomeDetailTransformer());
        }catch (\Exception $exception){ \Log::info($exception);
            return ResponeFails('异常错误');
        }
    }
    /**
     * 时间筛选条件
     *
     */
    private function searchDate($start_date,$end_date,$field)
    {
        $whereStr = '';
        if (!empty($start_date)){
            $whereStr  .= " and ".$field." >= '" . date('Y-m-d 00:00:00',strtotime($start_date)) . "'";
        }
        if (!empty($end_date)){
            $whereStr  .= " and ".$field." <= '" . date('Y-m-d 23:59:59',strtotime($end_date)) . "'";
        }
        return $whereStr;
    }

    /**
     * 首充查询
     * @param string $start_date    开始日期
     * @param string $end_date      结束时间
     * @param int    $user_id       用户id
     *
     */
    private function firstPay($start_date,$end_date,$user_id = null)
    {
        if (!empty($end_date)){
            $end_date = date('Y-m-d 23:59:59',strtotime($end_date));
        }
        $rightJoinSub = \DB::table(PaymentOrder::tableName())
            ->select('user_id',
                \DB::raw("min(id) as id"),
                \DB::raw("min(created_at) as ctime")
            )
            ->where('payment_status',PaymentOrder::SUCCESS)
            ->groupBy('user_id');
        $data = PaymentOrder::from('payment_orders as a')
            ->select(\DB::raw("count(t.id) as num"),\DB::raw("sum(a.coins) as coins"))
            ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
            ->rightJoinSub($rightJoinSub,'t', function($join){
                $join->on('t.id', '=', 'a.id');
            })
            ->andFilterBetweenWhere('t.ctime', $start_date, $end_date);
        if ($user_id){
            $data = $data->where('b.parent_user_id',$user_id);
        }else{
            $data = $data->where('b.parent_user_id','>',0);
        }

        $data = $data->first();
        return $data;
    }
}
