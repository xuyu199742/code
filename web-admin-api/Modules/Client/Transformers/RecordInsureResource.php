<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class RecordInsureResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'RecordID'      => $this->RecordID,
            'KindID'        => $this->KindID,
            'ServerID'      => $this->ServerID,
            'SourceUserID'  => $this->SourceUserID,//转账人
            'transfer'      => $this->transfer,//转账人
            'SourceGold'    => $this->SourceGold,
            'SourceBank'    => $this->SourceBank,
            'TargetUserID'  => $this->TargetUserID,//接收用户
            'receiver'      => $this->receiver,//接收用户
            'TargetGold'    => $this->TargetGold,
            'TargetBank'    => $this->TargetBank,
            'SwapScore'     => $this->SwapScore,
            'Revenue'       => $this->Revenue,
            'IsGamePlaza'   => $this->IsGamePlaza,
            'TradeType'     => $this->TradeType,
            'ClientIP'      => $this->ClientIP,
            'CollectDate'   => date('m-d H:i',strtotime($this->CollectDate)) ?? '',
            'CollectNote'   => date('m-d H:i',strtotime($this->CollectNote)) ?? '',
        ];
    }
}
