<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class TodayPayRankReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            "GameID"    => $item->GameID,
            "pay_money" => $item->pay_money ?? '0.00',
        ];
    }

}