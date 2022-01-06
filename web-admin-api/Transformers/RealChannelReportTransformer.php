<?php
/* 游戏报表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;


class RealChannelReportTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        $data = $item->toArray();
        $data['JettonSum'] = realCoins($data['JettonSum']);
        return $data;
    }

}