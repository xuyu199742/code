<?php
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\OuterPlatform\OuterPlatform;


class OuterPlatformTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            "id"            => !empty($item->id) ? $item->id : '',
            "name"          => $item->name ?? '',
            "description"   => $item->description ?? '',
            "sort"          => $item->sort ?? 0,
            "status"        => $item->status ?? OuterPlatform::STATUS_OFF,
            "alias"         => $item->alias,
            "icon"          => $item->icon ?? '',
            "web_icon"      => $item->web_icon,
            "icons"         => $item->icons ?? '',
            "img"           => $item->img ?? '',
            "server_status" => $item->server_status,
            "company_id"    => $item->company_id,
            "owned"         => $item->owned,
            'have_games'    => $item->have_games,
            "icon_url"      => !empty($item->icon) ? asset('storage/' . $item->icon) : '',
            "web_icon_url"  => !empty($item->web_icon) ? asset('storage/' . $item->web_icon) : '',
            "icons_url"     => !empty($item->icons) ? asset('storage/' . $item->icons) : '',
            "img_url"       => !empty($item->img) ? asset('storage/' . $item->img) : '',
            "created_at"    => !empty($item->created_at) ? date('Y-m-d H:i:s', strtotime($item->created_at)) : '',
            "updated_at"    => !empty($item->updated_at) ? date('Y-m-d H:i:s', strtotime($item->updated_at)) : '',
        ];
    }

}
