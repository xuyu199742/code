<?php
/*用户代理信息表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Agent\AgentInfo;
class AgentInfoTransformer extends TransformerAbstract
{
    public function transform(AgentInfo $item)
    {
        return [
            'id'                =>  $item->id,
            'user_id'           =>  $item->user_id,
            'balance'           =>  realCoins($item->balance),
            'created_at'        =>  $item->created_at,
            'updated_at'        =>  $item->updated_at,
        ];
    }

}