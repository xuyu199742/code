<?php
/*
 |--------------------------------------------------------------------------
 | 配置契约
 |--------------------------------------------------------------------------
 | Notes:
 | Class Config
 | User: Administrator
 | Date: 2019/7/18
 | Time: 14:37
 |
 |  * @return
 |  |
 |
 */

namespace Modules\Payment\Packages\ThirdPay\Interfaces;



interface Config
{
    public static function config();

    public function setConfig($config);



}
