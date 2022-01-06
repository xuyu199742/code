<?php

namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;

class FirstRechargeTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            'GameID'                =>  $item->GameID,
            'channel_id'            =>  $item->channel_id ?? '',
            'parent_user_id'        =>  $item->parent_user_id ? AccountsInfo::where('UserID',$item->parent_user_id)->value('GameID') : '',
            'first_amount'          =>  $item->first_amount ?? 0,
            'recharge_count'        =>  $item->recharge_count ?? 0,
            'recharge_amount'       =>  $item->recharge_amount ?? 0,
            'withdrawal_count'      =>  ($item->withdrawal_count ?? 0) + ($item->agent_count ?? 0),
            'withdrawal_amount'     =>  bcadd(($item->withdrawal_amount ?? 0) ,realCoins($item->agent_score ?? 0),2),
            'first_date'            =>  $item->first_date ? date('Y-m-d',strtotime($item->first_date)) : '',
            'RegisterDate'          =>  $item->RegisterDate ? date('Y-m-d',strtotime($item->RegisterDate)) : '',
        ];
    }

}
