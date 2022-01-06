<?php

namespace Modules\Client\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Models\Record\RecordTreasureSerial;

class AccountDetailTransformer extends TransformerAbstract
{
    public function transform(RecordTreasureSerial $item)
    {
        return [
            'DATE' => Carbon::parse($item->CollectDate ?? '')->format('Y-m-d H:i:s'),
            'FlowingType' => $item->TypeText, //流水类型
            'Amount' => realCoins($item->ChangeScore ?? 0),
            'AccountAmount' => realCoins(($item->CurScore + $item->ChangeScore) ?? 0), // 账户
        ];
    }
}
