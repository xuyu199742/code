<?php
/*  发送邮件列表 */

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Treasure\GameMailInfo;


class GameMailInfoTransformer extends TransformerAbstract
{
    //发送邮件列表
    public function transform(GameMailInfo $item)
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
            'Context'       => $item -> Context,
            'CreateTime'    => date('Y-m-d H:i:s',strtotime($item -> CreateTime)) ?? '',
            'username'      => $item->admin->username ?? '',
        ];
    }

}
