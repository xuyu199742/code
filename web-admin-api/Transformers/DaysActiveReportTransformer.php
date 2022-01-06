<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;


class DaysActiveReportTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            'DateTime' => $item->DateTime,
            'RegisterNum' => $item->RegisterNum,
            'LoginNum' => $item->LoginNum,
            'HighestNum' => $item->HighestNum,
            'two' => $item->two ? bcdiv( $item->two * 100,$item->RegisterNum,2) : 0,
            'three' => $item->three ? bcdiv($item->three * 100,$item->RegisterNum,2) : 0,
            'seven' => $item->seven ? bcdiv($item->seven * 100,$item->RegisterNum,2) : 0,
            'fifteen' => $item->fifteen ? bcdiv($item->fifteen * 100,$item->RegisterNum,2) : 0,
            'thirty' => $item->thirty ? bcdiv($item->thirty * 100,$item->RegisterNum,2) : 0
        ];
    }

}