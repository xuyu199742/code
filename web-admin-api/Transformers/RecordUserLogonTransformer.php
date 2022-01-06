<?php
/*游戏房间列表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Record\RecordUserLogon;


class RecordUserLogonTransformer extends  TransformerAbstract
{
    //登录日志
    public function transform(RecordUserLogon $item)
    {
        return $item->toArray();

    }
}