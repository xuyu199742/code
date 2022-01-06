<?php

namespace Transformers;
use League\Fractal\TransformerAbstract;

class JettonAnalysisTransformer extends TransformerAbstract
{

    public $index = 1;

    public function transform($item)
    {
        return [
            'number'                =>  ($this->index++) + request('number') ?? 0,
            'GameID'                =>  $item->GameID,
            'channel_id'            =>  $item->channel_id ?: '',
            'platform_name'         =>  request('platform_id') ? $item->name : '全部',
            'note_count'            =>  $item->note_count ?? 0,
            'jetton_score'          =>  realCoins($item->jetton_score ?? 0),
            'change_score'          =>  realCoins($item->change_score ?? 0),
        ];
    }

}
