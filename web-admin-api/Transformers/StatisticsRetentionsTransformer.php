<?php
/* 留存率*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\StatisticsRetentions;


class StatisticsRetentionsTransformer extends TransformerAbstract
{
    //留存率
    public function transform(StatisticsRetentions $item)
    {
        return $item->toArray();



    }

}