<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;


class ChannelPayReportTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            'DateTime' => $item->DateTime,
            'channel_id' => $item->channel_id ?? '/',
            'AccountsNum' => $item->AccountsNum,
            'LTV1' => bcadd($item->LTV1,0,2),
            'LTV2' => bcadd($item->LTV2,0,2),
            'LTV3' => bcadd($item->LTV3,0,2),
            'LTV4' => bcadd($item->LTV4,0,2),
            'LTV7' => bcadd($item->LTV7,0,2),
            'LTV30' => bcadd($item->LTV30,0,2),
            'LTV60' => bcadd($item->LTV60,0,2),
        ];
    }

}