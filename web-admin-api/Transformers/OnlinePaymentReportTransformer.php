<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class OnlinePaymentReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            "id"            => $item->id,
            "name"          => $item->name,
            "payment_num"   => $item->payment_num,
            "people_num"    => $item->people_num,
            "payment_money" => $item->payment_money,
        ];
    }

}