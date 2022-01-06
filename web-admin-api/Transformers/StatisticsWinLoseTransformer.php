<?php
/* 全局统计*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\StatisticsWinLose;


class StatisticsWinLoseTransformer extends TransformerAbstract
{
    //全局统计
    public function transform(StatisticsWinLose $item)
    {
        return $item->toArray();

        /*return [
            'UserID' => $item->UserID,
            'GameID' => $item->GameID,
            'SpreaderID' => $item->SpreaderID,
        ];*/

    }

}