<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Activity\RankingConfig;

class RankingConfigTransformer extends TransformerAbstract
{

    public function transform(RankingConfig $item)
    {
        return [
            "id"                => $item->id,
            "kind_name"         => $item->KindName ?? '',
            "server_name"       => $item->ServerName ?? '',
            "type_text"         => $item->type_text,
            "score"             => realCoins($item->score),
            "start_time"        => secondTransform($item->start_time),
            "end_time"          => secondTransform($item->end_time),
        ];
    }

}