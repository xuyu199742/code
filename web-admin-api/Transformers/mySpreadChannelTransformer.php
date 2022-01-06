<?php
/*渠道用户关联表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;


class mySpreadChannelTransformer extends TransformerAbstract
{
    //渠道用户表关联表
    public function transform($item)
    {
        return [
            'id'            => $item -> id,
            'channel_id'    => $item -> channel_id,
            'user_id'       => $item -> user_id,
            'game_id'       => $item -> account->GameID ?? '',
            'nickname'      => $item -> account->NickName ?? '',
            'last_logon_ip' => $item -> account->LastLogonIP ?? '',
            'created_at'    => date('Y-m-d H:i:s',strtotime($item -> created_at)) ?? '',
            'game_score'    =>  [
                'UserID'    => $item -> gameScore['UserID'],
                'WinScore'  => realCoins($item -> gameScore['WinScore'])
            ],
            'payment_order_sum_amount' => $item->paymentOrderSumAmount[0]->amount??0,
            'withdrawal_order_sum_amount' => $item ->withdrawalOrderSumAmount[0]->real_money??0,
        ];


    }

}
