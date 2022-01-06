<?php
/*渠道用户关联表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Agent\ChannelUserRelation;


class ChannelUserRelationTransformer extends TransformerAbstract
{
    //渠道用户表关联表
    public function transform(ChannelUserRelation $item)
    {
        return $item->toArray();
        /*return [
            'id'=>$item->id,
            'agent_channel_id'=>$item->agent_channel_id,
            'user_id'=>$item->user_id,
            'create_time'=>$item->create_time,
        ];*/
    }

}