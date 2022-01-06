<?php

namespace Transformers;
use League\Fractal\TransformerAbstract;

class RechargeWithdrawalTransformer extends TransformerAbstract
{

    public $index = 1;

    public function transform($item)
    {
        return [
            'number'                =>  ($this->index++) + request('number') ?? 0,
            'GameID'                =>  $item->GameID,
            'RegisterMobile'        =>  $item->RegisterMobile,
            'channel_id'            =>  $item->channel_id ?: '',
            'recharge_count'        =>  $item->recharge_count ?? 0,
            'recharge_amount'       =>  $item->recharge_amount ?? 0,
            'withdrawal_count'      =>  $item->withdrawal_count ?? 0,
            'withdrawal_amount'     =>  $item->withdrawal_amount ?? 0,
            'system_up_count'       =>  $item->system_up_count ?? 0,
            'system_up_amount'      =>  abs(realCoins($item->system_up_amount ?? 0)),
            'system_down_count'     =>  $item->system_down_count ?? 0,
            'system_down_amount'    =>  abs(realCoins($item->system_down_amount ?? 0)),
            'balance'               =>  realCoins($item->balance ?? 0),
            'change_score'          =>  realCoins($item->change_score ?? 0),
            'agent_withdrawal_count'    =>  $item->agent_count ?? 0,
            'agent_withdrawal_amount'   =>  realCoins($item->agent_score ?? 0),
        ];
    }

}
