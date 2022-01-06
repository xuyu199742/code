<?php
/*金币流水记录表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Record\RecordTreasureSerial;

class AuditBetScoreTransformer extends  TransformerAbstract
{

    public function transform(RecordTreasureSerial $item)
    {
        return [
            'CollectDate'       => date('Y-m-d H:i:s',strtotime($item->CollectDate)),
            'GameID'            => $item->account->GameID,
            'order_no'          => $item->order ? $item->order->order_no : '',
            'TypeText'          => $item->TypeText,
            'ChangeScore'       => realCoins($item->ChangeScore ?? 0),
            'CurAuditBetScore'  => realCoins($item->CurAuditBetScore ?? 0),
            'Reason'            => $item->Reason ?: '',
        ];
    }

}