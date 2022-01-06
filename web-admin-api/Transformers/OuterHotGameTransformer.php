<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;

class OuterHotGameTransformer extends TransformerAbstract
{

    public function transform($item)
    {
            return [
                'id'            => $item->id,
                'platform_id'   => $item->platform_id,
                'kind_id'       => $item->kind_id,
                'icon'          => $item->icon,
                'icon_url'      => $item->icon_url,
                'platform_name' => $item->platform ? $item->platform->name : '',
                'game_name'     => $item->name,
                'sort'          => $item->HotSort,
                'status'        => $item->status,
                'created_at'    => $item->created_at ? date('Y-m-d H:i:s',strtotime($item->created_at)) : '',
                'updated_at'    => $item->updated_at ? date('Y-m-d H:i:s',strtotime($item->updated_at)) : '',
            ];

    }



}
