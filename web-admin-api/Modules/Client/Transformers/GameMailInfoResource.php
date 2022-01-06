<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class GameMailInfoResource extends Resource
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
            'ID'                    => $this->ID,
            'Title'                 => $this->Title,
            'Context'               => $this->Context,
            'CreateTime'            => date('Y-m-d H:i:s',strtotime($this->CreateTime)),
            //'IsRead'                => $this->IsRead,
            //'IsDelete'              => $this->IsDelete,
            //'AllUserIndex'          => $this->AllUserIndex,
        ];
    }
}
