<?php
/*
 |--------------------------------------------------------------------------
 | 定义支付类型接口
 |--------------------------------------------------------------------------
 | Notes:
 | Class PayTypes
 | User: Administrator
 | Date: 2019/7/15
 | Time: 15:17
 |
 |  * @return
 |  |
 |
 */

namespace Modules\Payment\Packages\ThirdPay\Interfaces;


interface CallBack
{
    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool;

    /**
     * 回调成功处理
     * @return mixed
     */
    public function success();

    /**
     * 回调失败
     * @return mixed
     */
    public function fail();

}
