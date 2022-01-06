<?php
namespace Transformers;

use League\Fractal\TransformerAbstract;

class OuterPlatformGameTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            "id"             => $item->id,
            "icon"           => $item->icon,
            "icons"          => $item->icons ?? '',
            "img"            => $item->img ?? '',
            "name"           => $item->name,
            "platform_id"    => $item->platform_id,
            "description"    => $item->description,
            "sort"           => $item->sort,
            "status"         => $item->status,
            "created_at"     => !empty($item->created_at) ? date('Y-m-d H:i:s', strtotime($item->created_at)) : '',
            "updated_at"     => !empty($item->updated_at) ? date('Y-m-d H:i:s', strtotime($item->updated_at)) : '',
            "kind_id"        => $item->kind_id,
            "type"           => $item->type,
            "platform_alias" => $item->platform_alias,
            "server_status"  => $item->server_status,
            "icon_url"       => !empty($item->icon) ? asset('storage/' . $item->icon) : '',
            "icons_url"      => !empty($item->icons) ? asset('storage/' . $item->icons) : '',
            "img_url"        => !empty($item->img) ? asset('storage/' . $item->img) : '',
        ];
    }

}
