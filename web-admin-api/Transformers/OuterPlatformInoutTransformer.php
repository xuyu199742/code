<?php
namespace Transformers;

use League\Fractal\TransformerAbstract;


class OuterPlatformInoutTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            "id"                => $item->id,
            "user_id"           => $item->user_id,
            "game_id"           => $item->game_id,
            "login_ip"          => $item->login_ip,
            "carry_score"       => realCoins($item->carry_score),
            "created_at"        => date('Y-m-d H:i:s',strtotime($item->created_at)),
            "updated_at"        => !empty($item->updated_at) && $item->status > 0 ? date('Y-m-d H:i:s',strtotime($item->updated_at)) : '',
            "quit_ip"           => $item->quit_ip,
            "quit_score"        => $item->status != 0 ? realCoins($item->quit_score) : '',
            //"platform_id"       => $item->platform_id,
            "platform_name"     => $item->platform_name ?? '',
            "kind_name"         => $item->kind_name ?? '',
            'chage_score'       => $item->status != 0 ? realCoins($item->quit_score - $item->carry_score) : '',

        ];
    }

}
