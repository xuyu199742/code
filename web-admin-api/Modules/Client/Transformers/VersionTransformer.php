<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class VersionTransformer extends Resource
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
            'version_id'       => (int)$this->version_id,
            'hot_update_url'   => $this->hot_update_url,
            'force_update_url' => $this->force_update_url,
            'status'           => (boolean)$this->status,
        ];
    }
}
