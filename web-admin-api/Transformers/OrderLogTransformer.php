<?php
/* 订单日志*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\OrderLog;


class OrderLogTransformer extends TransformerAbstract
{
    //订单日志
    public function transform(OrderLog $item)
    {
        if(isset($item->admin->username)){
            $item->username = $item->admin->username;
        }
        else{
            $item->username = '玩家';
        }
        return $item->toArray();
    }

}