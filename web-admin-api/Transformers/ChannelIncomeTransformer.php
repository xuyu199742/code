<?php
/*渠道收入信息表*/

namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Agent\ChannelIncome;


class ChannelIncomeTransformer extends TransformerAbstract
{
    //渠道收入信息表
    public function transform(ChannelIncome $item)
    {
        //return $item->toArray();
        return [
            'id'=>$item->id,
            'channel_id'=>$item->channel_id,
            'record_type'=>$item->record_type,
            'user_id'=>$item->user_id,
            'value'=>$item->value,
            'return_value'=>$item->return_value,
            'kind_id'=>$item->kind_id,
            'server_id'=>$item->server_id,
            'created_at'=>$item->created_at,
        ];
    }

}