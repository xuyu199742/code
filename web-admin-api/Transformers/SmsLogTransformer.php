<?php
/* 短信日志*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\SmsLog;


class SmsLogTransformer extends TransformerAbstract
{
    //短信日志
    public function transform(SmsLog $item)
    {
        return $item->toArray();
    }

}