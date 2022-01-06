<?php
/*用户银行存取记录*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Treasure\RecordInsure;
class RecordInsureTransformer extends  TransformerAbstract
{
    public function transform(RecordInsure $item)
    {
        return [
            'RecordID'      => $item->RecordID,
            'KindID'        => $item->KindID,
            'ServerID'      => $item->ServerID,
            'SourceUserID'  => $item->SourceUserID,
            'SourceGold'    => realCoins($item->SourceGold),
            'SourceBank'    => realCoins($item->SourceBank),
            'TargetUserID'  => $item->TargetUserID,
            'TargetGold'    => realCoins($item->TargetGold),
            'TargetBank'    => realCoins($item->TargetBank),
            'SwapScore'     => realCoins($item->SwapScore),
            'Revenue'       => realCoins($item->Revenue),
            'IsGamePlaza'   => $item->IsGamePlaza,
            'TradeType'     => $item->TradeType,
            'ClientIP'      => $item->ClientIP,
            'CollectDate'   => date('Y-m-d H:i:s',strtotime($item->CollectDate)) ?? '',
            'CollectNote'   => $item->CollectNote,
            'transfer'      => $item->transfer->GameID ?? '' ,//转账人
            'receiver'      => $item->receiver->GameID ?? '' ,//收款人
        ];
    }

}