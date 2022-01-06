<?php
/* 登录日志*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\LoginLog;


class LoginLogTransformer extends TransformerAbstract
{
    //登录日志
    public function transform(LoginLog $item)
    {
        if(isset($item->admin->username)){
            $item->username = $item->admin->username;
        }
        return $item->toArray();
    }

}