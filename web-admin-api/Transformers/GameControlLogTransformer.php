<?php
/* 订单日志*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\GameControlLog;

class GameControlLogTransformer extends TransformerAbstract
{
    //订单日志
    public function transform(GameControlLog $item)
    {
        return [
            'id'            =>  $item->id,
            'admin_id'      =>  $item->admin_id,
            'title'         =>  $item->title,
            'ip'            =>  $item->ip,
            'create_time'   =>  date('Y-m-d H:i:s',strtotime($item->create_time)),
            'details'       =>  $item->details,
            'username'      =>  $item->admin->username,
            'status'        =>  $item->status,
            'status_text'   =>  $item->StatusText,
        ];
    }

}
