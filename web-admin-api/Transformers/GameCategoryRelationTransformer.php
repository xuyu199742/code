<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;

class GameCategoryRelationTransformer extends TransformerAbstract
{

    public function transform($item)
    {
            return [
                'id'            => $item->id,
                'category_id'   => $item->category_id,
                'platform_id'   => $item->platform_id,
                'icon'          => $item->platform->icon ?? '',
                'web_icon'      => $item->platform->web_icon ?? '',
                'icons'         => $item->platform->icons ?? '',
                'img'           => $item->platform->img ?? '',
                'icon_url'      => !empty($item->platform->icon) ? asset('storage/' . $item->platform->icon) : '',
                'web_icon_url'  => !empty($item->platform->web_icon) ? asset('storage/' . $item->platform->web_icon) : '',
                'icons_url'     => !empty($item->platform->icons) ? asset('storage/' .$item->platform->icons) : '',
                'img_url'       => !empty($item->platform->img) ? asset('storage/' .$item->platform->img) : '',
                'name'          => $item->platform->name ?? '',
                "description"   => $item->platform->description ?? '',
                'sort'          => $item->sort,
                'status'        => $item->platform->status,
                'server_status' => $item->platform->server_status ?? '',
                'have_games'    => $item->platform->have_games ?? 1,
                "alias"         => $item->platform->alias,
            ];

    }



}
