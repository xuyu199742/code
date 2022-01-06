<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class PayUserKeepReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            "channel_id"    => $item->channel_id ?? '',
            "ctime"         => $item->ctime,
            "num"           => $item->num,
            "two"           => $item->two,
            "three"         => $item->three,
            "seven"         => $item->seven,
            "fifteen"       => $item->fifteen,
            "thirty"        => $item->thirty,
            "sixty"         => $item->sixty ?? 0,
        ];
    }

}