<?php

namespace Modules\Payment\Packages\ThirdPay\Facades;

use Illuminate\Support\Facades\Facade;

class Pay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Modules\Payment\Packages\ThirdPay\Pay::class;
    }
}
