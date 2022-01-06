<?php
/* 游戏公告*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\SystemNotice;


class SystemNoticeTransformer extends TransformerAbstract
{
    //游戏公告
    public function transform(SystemNotice $item)
    {
        return $item->toArray();



    }

}