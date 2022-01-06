<?php
/* 系统日志*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\SystemLog;


class SystemLogTransformer extends TransformerAbstract
{
    //系统日志
    public function transform(SystemLog $item)
    {
        if(isset($item->admin->username)){
            $item->username = $item->admin->username;
        }
        return $item->toArray();
    }

}