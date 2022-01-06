<?php
namespace Modules\Agent\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentIncome;
use Models\Agent\AgentRelation;
use Models\Agent\AgentWithdrawRecord;
use Models\Treasure\RecordScoreDaily;
use Models\Treasure\RecordScoreDailyEx;
use Transformers\AgentIncomeTransformer;
use Transformers\AgentRelationTransformer;
use Validator;
class AgentController extends BaseController
{
    /**
     * 代理列表-查所有代理
     * @return Response
     */
    public function getList(Request $request)
    {
        Validator::make(request()->all(), [
            'game_id'         => ['nullable','numeric'],
            'parent_game_id'  => ['nullable','numeric'],
        ], [
            'game_id.numeric' => '代理ID必须是数字，请重新输入！',
            'parent_game_id.numeric' => '上级代理ID必须是数字，请重新输入！',
        ])->validate();

        //如果父级条件不存在的话，查询已绑定下级代理的代理用户，并统计直属下级绑定人数
        $obj = AgentRelation::from('agent_relation as a')
            ->with('account:UserID,GameID,NickName','agentinfo:user_id,balance','agentAccount:UserID,GameID')
            ->select('a.user_id', 'a.parent_user_id');
        //如果父级条件不存在的话，查询出所有直属子集，未绑定的也显示
        if (!request('parent_game_id') && !request('game_id')){
            $obj = $obj->join('agent_relation as b','a.user_id','=','b.parent_user_id');
        }
        $obj = $obj->orderBy('a.user_id','desc')->groupBy('a.user_id','a.parent_user_id');
        //根据gameID查询
        $obj = $this->gameIdSearchUserId(request('game_id'), $obj,'a.user_id');
        //根据父级的gameID查询
        $obj = $this->gameIdSearchUserId(request('parent_game_id'), $obj,'a.parent_user_id');
        //dd($obj->toSql());
        //得到分页列表
        $list = $obj->paginate(10);
        //得到该分页的ID集合，作为父级ID集合
        $parent_ids = $list->pluck('user_id')->toArray();
        //查询分页中的ID直属的
        list($sum_pay,$sum_pay_people,$sum_withdraw,$info,$bind_phone_num,$sub_people) = $this->zhishu_childs($parent_ids);

        //查询直属下级推广情况
        foreach ($list as $k => $v){
            $list[$k]['parent_game_id']  = $v->agentAccount['GameID'] ?? '';//获取父级代理的game_id
            $list[$k]['sum_promote']     = $sub_people[$v->user_id]['sub_people'] ?? 0;//代理直属下级的推广人数
            $list[$k]['sum_pay']         = $sum_pay[$v->user_id]['sum_pay'] ?? 0;//代理直属下级的充值
            $list[$k]['sum_pay_people']  = $sum_pay_people[$v->user_id]['sum_pay_people'] ?? 0;//代理直属下级的充值人数
            $list[$k]['sum_bind_people'] = $bind_phone_num[$v->user_id]['bind_phone_num'] ?? 0;//代理直属下级的绑定人数
            $list[$k]['sum_withdraw']    = $sum_withdraw[$v->user_id]['sum_withdraw'] ?? 0;//代理直属下级
            $list[$k]['sum_revenue']     = $info[$v->user_id]['sum_revenue'] ?? 0;//代理直属下级的税收
            $list[$k]['sum_winlos']      = $info[$v->user_id]['sum_winlos'] ?? 0;//代理直属下级的输赢
            $list[$k]['sum_bet']         = $info[$v->user_id]['sum_bet'] ?? 0;//代理直属下级的下注
            $list[$k]['sum_water']       = $info[$v->user_id]['sum_water'] ?? 0;//代理直属下级的流水
        }
        return $this->response->paginator($list,new AgentRelationTransformer());
    }

    /**
     * 代理周业绩列表统计
     * @return Response
     */
    public function weekEnterpriseList()
    {
        $list = AgentIncome::where('user_id',request('user_id'))->orderBy('end_date','desc')->paginate(10);
        return $this->response->paginator($list,new AgentIncomeTransformer());
    }

    //直属推广情况,多条的，列表数据分组计算的
    private function zhishu_childs($parent_ids)
    {
        //查询直属下级人数
        $sub_people = AgentRelation::select('parent_user_id', \DB::raw('count(*) as sub_people'))
            ->whereIn('parent_user_id',$parent_ids)//直属推广
            ->groupBy('parent_user_id')
            ->get()->keyBy('parent_user_id');

        //代理推广的充值
        $sum_pay = PaymentOrder::from('payment_orders as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
            ->select('b.parent_user_id', \DB::raw('sum(a.coins) as sum_pay'))
            ->where('a.payment_status', PaymentOrder::SUCCESS)
            ->whereIn('b.parent_user_id',$parent_ids)//直属推广
            ->groupBy('b.parent_user_id')
            ->get()->keyBy('parent_user_id');

        //代理推广的充值人数
        $sum_pay_people = PaymentOrder::from('payment_orders as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
            ->select('b.parent_user_id', \DB::raw('count(distinct(a.user_id)) as sum_pay_people'))
            ->where('a.payment_status', PaymentOrder::SUCCESS)
            ->whereIn('b.parent_user_id',$parent_ids)//直属推广
            ->groupBy('b.parent_user_id')
            ->get()->keyBy('parent_user_id');


        $sum_withdraw = AgentWithdrawRecord::from('agent_withdraw_record as a')
            ->leftJoin(AgentRelation::tableName().' as b','a.user_id','=','b.user_id')
            ->select('b.parent_user_id', \DB::raw('sum(a.score) as sum_withdraw'))
            ->where('a.status', AgentWithdrawRecord::PAY_SUCCESS)
            ->whereIn('b.parent_user_id',$parent_ids)//直属推广
            ->groupBy('b.parent_user_id')
            ->get()->keyBy('parent_user_id');
        /*$sum_withdraw = WithdrawalOrder::from('withdrawal_orders as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
            ->select('b.parent_user_id', \DB::raw('sum(a.real_gold_coins) as sum_withdraw'))
            ->where('a.status', WithdrawalOrder::PAY_SUCCESS)
            ->whereIn('b.parent_user_id',$parent_ids)//直属推广
            ->groupBy('b.parent_user_id')
            ->get()->keyBy('parent_user_id');*/

        //代理推广的税收、输赢、下注、流水
        $info = RecordScoreDaily::from('RecordScoreDaily as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','A.UserID','=','b.user_id')
            ->select('b.parent_user_id',
                \DB::raw('sum(a.SystemServiceScore) as sum_revenue'), //代理推广的税收
                \DB::raw('sum(a.RewardScore-a.JettonScore) as sum_winlos'), //代理推广的输赢改为：直推玩家输赢=中奖-投注
                \DB::raw('sum(a.JettonScore) as sum_bet'), //代理推广的下注
                \DB::raw('sum(a.StreamScore) as sum_water') //代理推广的流水
            )
            ->whereIn('b.parent_user_id',$parent_ids)//直属推广
            ->groupBy('b.parent_user_id')
            ->get()->keyBy('parent_user_id');

        //获取绑定人数
        $bind_phone_num = AccountsInfo::from('AccountsInfo as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','a.UserID','=','b.user_id')
            ->select('b.parent_user_id',\DB::raw('count(*) as bind_phone_num'))
            ->where('a.RegisterMobile','<>','')//绑定手机号
            ->whereIn('b.parent_user_id',$parent_ids)//直属推广
            ->groupBy('b.parent_user_id')
            ->get()->keyBy('parent_user_id');

        return [$sum_pay,$sum_pay_people,$sum_withdraw,$info,$bind_phone_num,$sub_people];
    }

    /**
     * 查询指定代理用户的整条线的相关数据（不包含个人）
     *
     * @param int $user_id 用户的user_id
     * @param int $type 1整线，2直属下线   默认整线
     *
     */
    private function agent_all_counts($user_id, $type = 1)
    {
        //根据类型判断条件
        switch ($type){
            case 1:
                $where = ['b.rank','like','%,'.$user_id.',%'];
                break;
            case 2:
                $where = ['b.parent_user_id','=',$user_id];
                break;
            default:
                $where = ['b.parent_user_id','=',$user_id];
                break;
        }
        //代理推广的整条线的人数
        $data['team_ids'] = AgentRelation::from('payment_orders as b')->where($where)->count();

        //代理推广的充值
        $data['sum_pay'] = PaymentOrder::from('payment_orders as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
            ->where('a.payment_status', PaymentOrder::SUCCESS)
            ->where($where)
            ->sum('a.coins');

        //代理推广的充值人数
        $data['sum_pay_people'] = PaymentOrder::from('payment_orders as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
            ->where('a.payment_status', PaymentOrder::SUCCESS)
            ->where($where)
            ->distinct('a.user_id')->count();


        $data['sum_withdraw'] = WithdrawalOrder::from('withdrawal_orders as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','a.user_id','=','b.user_id')
            ->where($where)->sum('a.real_gold_coins');

        //代理推广的税收、输赢、下注、流水
        $data['info'] = RecordScoreDaily::from('RecordScoreDaily as a')
            ->leftJoin('AgentDB.dbo.agent_relation AS b','A.UserID','=','b.user_id')
            ->select(
                \DB::raw('sum(a.SystemServiceScore) as sum_revenue'), //代理推广的税收
                \DB::raw('sum(a.ChangeScore) as sum_winlos'), //代理推广的输赢
                \DB::raw('sum(a.JettonScore) as sum_bet'), //代理推广的下注
                \DB::raw('sum(a.StreamScore) as sum_water') //代理推广的流水
            )
            ->where($where)->first();
        return $data;
    }

}

