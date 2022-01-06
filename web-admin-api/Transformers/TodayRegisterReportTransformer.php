<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class TodayRegisterReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            "user_id"           => $item->UserID,
            "game_id"           => $item->GameID,
            "reg_date"          => date('Y-m-d H:i:s',strtotime($item->RegisterDate)) ?? '',
            "channel_id"        => $item->channel_id ?? '',
            "payment_money"     => $item->payment_money,
            "withdraw_money"    => $item->withdraw_money,
        ];
    }

}