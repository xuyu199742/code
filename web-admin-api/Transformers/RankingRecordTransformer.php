<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Activity\RankingRecord;

class RankingRecordTransformer extends TransformerAbstract
{

    public function transform(RankingRecord $item)
    {
        return [
            'id'        => $item->id,
            'sort'      => $item->sort,
            'game_id'   => $item->GameID,
            'water'     => realCoins($item->water),
            'winlose'   => realCoins($item->winlose),
            'bet'       => realCoins($item->bet),
            'score'     => realCoins($item->score),
            'num'       => $item->num,
        ];
    }

}