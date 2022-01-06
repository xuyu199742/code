<?php

namespace Modules\Client\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Models\OuterPlatform\WashCodeHistory;

class GetWashCodeHistoryTransformer extends TransformerAbstract
{
    public function transform(WashCodeHistory $item)
    {
        return [
            'Id' => $item->id,
            'Date' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
            'JettonScore' => realCoins($item->records->sum('jetton_score') ?? 0), //有效投注
            'WashCodeScore' => realCoins($item->records->sum('wash_code') ?? 0), //洗码
        ];
    }
}
