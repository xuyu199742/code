<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Record\RecordTreasureSerial;


class AnalysisCashGiftsTransformer extends TransformerAbstract
{
    public $index = 1;
    public function transform($item)
    {
        return [
            'number'         =>  ($this->index++) + request('number') ?? 0,
            'game_id'        => $item->GameID,
            'channel_id'     => $item->channel_id ?? '/',
            'type_text'      => $item->TypeID ? RecordTreasureSerial::getTypes(2)[$item->TypeID] : '全部',
            'total'          => $item->cash_gift_total ?? 0,
            'sum_score'      => realCoins($item->cash_gift_score ?? 0),
        ];
    }

}
