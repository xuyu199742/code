<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Agent\AgentInfo;
use Models\Agent\AgentRelation;
class AgentRelationTransformer extends TransformerAbstract
{
   // protected $availableIncludes = ['account','agentinfo'];

    public function transform(AgentRelation $item)
    {
        $agentinfo = $item->agentinfo;
        $agentinfo['balance'] = realCoins($agentinfo['balance'] ?? 0);
        return [
           // 'id'                =>  $item->id,
            'user_id'           =>  $item->user_id,
            'parent_user_id'    =>  $item->parent_user_id,
           // 'rank'              =>  $item->rank,
           // 'created_at'        =>  $item->created_at,
            'parent_game_id'    =>  $item->parent_game_id,
            'sum_promote'       =>  $item->sum_promote,//总推广人数
            'sum_pay_people'    =>  $item->sum_pay_people,//总充值人数
            'sum_bind_people'    =>  $item->sum_bind_people,//绑定人数
            'sum_pay'           =>  realCoins($item->sum_pay ?? 0),//总充值
            'sum_withdraw'      =>  realCoins($item->sum_withdraw ?? 0),
            'sum_revenue'       =>  realCoins($item->sum_revenue ?? 0),//总税收
            'sum_winlos'        =>  realCoins($item->sum_winlos ?? 0),//总输赢
            'sum_bet'           =>  realCoins($item->sum_bet ?? 0),//统计总下注
            'sum_water'         =>  realCoins($item->sum_water ?? 0),//统计总流水
            'account'           =>  $item->account,//用户信息
            'agentinfo'         =>  $agentinfo,//代理信息
        ];
    }


    /*关联用户信息表数据*/
    public function includeAccount(AgentRelation $item)
    {
        if(isset($item->account)){
            return $this->primitive($item->account,new AccountsInfoTransformer());
        }else{
            return $this->primitive(new AccountsInfo(),new AccountsInfoTransformer);
        }
    }

    /*关联用户代理信息表数据*/
    public function includeAgentinfo(AgentRelation $item)
    {
        if(isset($item->agentinfo)){
            return $this->primitive($item->agentinfo,new AgentInfoTransformer());
        }else{
            return $this->primitive(new AgentInfo(),new AgentInfoTransformer());
        }
    }

}
