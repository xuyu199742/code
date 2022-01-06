<?php

namespace Modules\Client\Transformers;

use League\Fractal\TransformerAbstract;

use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\WashCodeRecord;

class GetWashCodeRecordTransformer extends TransformerAbstract
{
    public function transform(WashCodeRecord $item)
    {
        return [
            'JettonScore' => realCoins($item->jetton_score ?? 0), //有效投注
            'WashCode' => realCoins($item->wash_code ?? 0), //洗码
            'WashCodeProportion' => (float)bcadd($item->Retio, 0, 2), //洗码比例
            'PlatformName' => optional($item->platform)->name ?? "",
        ];
    }
}
