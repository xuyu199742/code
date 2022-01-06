<?php
/*用户信息表*/

namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Agent\ChannelInfo;


class ChannelInfoTransformer extends TransformerAbstract
{
    //用户表基本信息
    public function transform(ChannelInfo $item)
    {
        return $item->toArray();
       /* return [
            'id'=>$item->id,
            'channel_id'=>$item->channel_id,
            'channel_domain'=>$item->channel_domain,
            'created_at'=>$item->created_at,
            'updated_at'=>$item->updated_at,
        ];*/
    }

}