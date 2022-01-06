<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Record\RecordTreasureSerial;


class ActivityCashDetailsTransformer extends TransformerAbstract
{
    public $index = 1;
    public function transform(RecordTreasureSerial $item)
    {
        return [
            'Sort'      =>  ($this->index++) + request('number') ?? 0,
            'TypeText'  => $item->TypeText,
            'Total'     => $item->total ?? 0,
            'SumScore'  => realCoins($item->sum_score ?? 0),
        ];
    }

}