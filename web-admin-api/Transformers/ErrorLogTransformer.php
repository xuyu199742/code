<?php
/* 错误日志*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\ErrorLog;


class ErrorLogTransformer extends TransformerAbstract
{
    //错误日志
    public function transform(ErrorLog $item)
    {
        return $item->toArray();
    }

}