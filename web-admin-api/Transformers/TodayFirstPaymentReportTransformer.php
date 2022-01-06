<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class TodayFirstPaymentReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            "coins"         =>  $item->amount,
            "created_at"    =>  date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',
            "game_id"       =>  $item->GameID,
            "reg_date"      =>  date('Y-m-d H:i:s',strtotime($item->RegisterDate)) ?? '',
        ];
    }

}