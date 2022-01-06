<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Record\RecordTreasureSerial;

class ActivityReportTransformer extends TransformerAbstract
{

    public function transform(RecordTreasureSerial $item)
    {
        return [
            "TypeID"        => $item->TypeID,
            "TypeText"      => $item->TypeText,
            "ChangeScore"   => realCoins($item->ChangeScore),
            "num"           => $item->num,
            "people_num"    => $item->people_num,
        ];
    }

}