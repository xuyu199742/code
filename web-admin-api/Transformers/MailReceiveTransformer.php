<?php
/*  用户发送邮件(收件箱) */

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Treasure\GameMailInfoReceive;


class MailReceiveTransformer extends TransformerAbstract
{
    //用户发送邮件(收件箱)
    public function transform(GameMailInfoReceive $item)
    {
       // return $item->toArray();
        return [
            'ID'            => $item -> ID,
            'UserID'        => $item -> UserID,
            'admin_id'      => $item -> admin_id,
            'GameID'        => $item -> account->GameID ?? '',
            'NickName'      => $item -> account->NickName ?? '',
            'Title'         => $item -> Title,
            'IsRead'        => $item -> IsRead,
            'IsReply'       => $item -> IsReply,
            'Context'       => $item -> Context,
            'CreateTime'    => date('Y-m-d H:i:s',strtotime($item -> CreateTime)) ?? '',
            'username'      => $item->admin->username ?? '',
        ];
    }

}
