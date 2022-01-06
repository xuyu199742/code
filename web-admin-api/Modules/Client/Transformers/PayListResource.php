<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class PayListResource extends Resource
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
            'user_id'       => $this->user_id,
            'amounts'       => $this->amounts,
            'NickName'      => $this->NickName,
            'created_at'    => $this->created_at ? $this->created_at->format('m-d') : '',
        ];
    }
}
