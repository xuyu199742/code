<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\JsonResource as Resource;

class ReturnRebateTransformer extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'return_id'     => $this->id,
            'user_id'       => $this->user_id,
            'scale'         => (bcdiv($this->score,$this->loss_score,2) * 100).'%',
            'score'         => realCoins($this->score),
            'loss_score'    => realCoins($this->loss_score),
        ];
    }
}
