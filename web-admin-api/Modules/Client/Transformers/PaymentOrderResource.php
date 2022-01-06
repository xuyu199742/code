<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class PaymentOrderResource extends Resource
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
            'order_no'   => $this->order_no,
            'status'     => $this->status_text,
            'amount'     => $this->amount,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
