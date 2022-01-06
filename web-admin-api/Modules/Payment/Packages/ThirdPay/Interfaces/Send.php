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

use Models\AdminPlatform\PaymentOrder;

interface Send
{
    /**
     * 请求四方订单方法
     *
     * @param PaymentOrder $order
     *
     * @return bool
     */
    public function send(PaymentOrder &$order) : bool;

    /**
     * 处理订单页面
     *
     * @param PaymentOrder $order
     *
     * @return mixed
     */
    public function view(PaymentOrder $order);


    /**
     * 查询订单
     * @param PaymentOrder $order
     *
     * @return mixed
     */
    public function queryOrder(PaymentOrder $order);

    /**
     * sdk支付
     * @param PaymentOrder $order
     * @return mixed
     */
    //public function sdk(PaymentOrder $order);

}
