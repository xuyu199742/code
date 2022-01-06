<?php
/* 玩家登陆日志*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\UserLogonLog;


class UserLogonLogTransformer extends TransformerAbstract
{
    //玩家登陆日志
    public function transform(UserLogonLog $item)
    {
        return [
            'id'            =>  $item->id,
            'game_id'       =>  $item->GameID,
            'MemberOrder'   =>  $item->MemberOrder,
            'info'          =>  $item->info,
            'ip_addr'       =>  $item->ip_addr,
            'create_date'   =>  date('Y-m-d H:i:s',strtotime($item->create_date)),
            'RegDeviceName' =>  $item->device_name ?? '',
            'SystemVersion' =>  $item->system_version ?? '',
        ];
    }

}
