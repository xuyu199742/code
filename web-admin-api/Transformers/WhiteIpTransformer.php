<?php
/*  白名单管理 */

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\WhiteIp;


class WhiteIpTransformer extends TransformerAbstract
{
    //白名单管理
    public function transform(WhiteIp $item)
    {
        return $item->toArray();
    }

}
