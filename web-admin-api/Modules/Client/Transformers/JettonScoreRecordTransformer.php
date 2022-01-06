<?php

namespace Modules\Client\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Models\Treasure\RecordGameScore;

class JettonScoreRecordTransformer extends TransformerAbstract
{

    public function transform(RecordGameScore $item)
    {
        return [
            'Date' => Carbon::parse($item->OrderTime ?? '')->format('Y-m-d H:i:s'), //结算时间
            'OrderNo' => $item->OrderNo ?? '', //注单号
            'JettonScore' => realCoins($item->JettonScore ?? 0), // 投注
            'ChangeScore' => realCoins($item->ChangeScore ?? 0), // 输赢
            'GameName' => $item->game_name ?? '', //游戏名字
        ];
    }
}
