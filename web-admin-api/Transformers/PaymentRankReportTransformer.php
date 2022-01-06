<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class PaymentRankReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            "GameID"            => $item->GameID,
            "RegisterDate"      => date('Y-m-d H:i:s',strtotime($item->RegisterDate)) ?? '',
            "RegisterMobile"    => $item->RegisterMobile,
            "LastLogonDate"     => date('Y-m-d H:i:s',strtotime($item->LastLogonDate)) ?? '',
            "Score"             => realCoins($item->Score),
            "InsureScore"       => realCoins($item->InsureScore),
            "payment_money"     => $item->payment_money,
            "withdraw_money"    => $item->withdraw_money,
            "win_money"         => realCoins($item->win_money),
            "channel_id"        => $item->channel_id ?? '',
        ];
    }

}