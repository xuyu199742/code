<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class WithdrawalOrderResource extends Resource
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
            'order_no'    => $this->order_no,
            'card_no'     => $this->card_no,
            'payee'       => $this->payee,
            'bank_info'   => $this->bank_info,
            'phone'       => $this->phone,
            'gold_coins'  => $this->gold_coins,
            'status_text' => $this->status_text,
            'status'      => $this->status,
            'created_at'  => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '',
            'remark'      => $this->remark ? $this->remark : '',
        ];
    }
}
