<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;

class RechargeWechatTransformer extends TransformerAbstract
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function transform($item)
    {
        if (isset($item->code_address)) {
            $item->code_editress=$item->code_address;
            $item->code_address=asset('storage/'.$item->code_address);
        }
        return $item->toArray();
    }
}
