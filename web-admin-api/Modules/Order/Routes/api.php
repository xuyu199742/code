<?php

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->group(['prefix' => 'order', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Order\Http\Controllers'], function ($api) {
        //充值订单列表
        $api->get('payments', ['uses' => 'PaymentOrderController@index', 'permission' => ['admin', '充值订单列表', '用户订单管理', 'v1']])->name('order.payments');
        //内部充值订单列表
        $api->get('official', ['uses' => 'PaymentOrderController@official', 'permission' => ['admin', '内部充值订单列表', '用户订单管理', 'v1']])->name('order.official.payments');
        //vip订单列表
        $api->get('vip/list', ['uses' => 'PaymentOrderController@vip', 'permission' => ['admin', 'vip商人订单列表', '用户订单管理', 'v1']])->name('order.vip.payments');
        //vip商人充值
        $api->post('vip/pay', ['uses' => 'PaymentOrderController@vipPay', 'permission' => ['admin', 'vip商人充值', '用户订单管理', 'v1']])->name('order.vip.payments.pay');
        //内部充值添加金币
        $api->post('official/status/{status}', ['uses' => 'PaymentOrderController@officialStatus', 'permission' => ['admin', '内部充值添加金币', '用户订单管理', 'v1']])->name('order.official.status');
        //订单列表
        $api->get('withdrawals', ['uses' => 'WithdrawalOrderController@index', 'permission' => ['admin', '提现订单列表', '用户订单管理', 'v1']])->name('order.withdrawals');
        //财务订单列表
        $api->get('finance/list', ['uses' => 'WithdrawalOrderController@financeList', 'permission' => ['admin', '财务订单列表', '用户订单管理', 'v1']])->name('order.finance.list');
        //客服审核通过订单
        $api->post('services/pass', ['uses' => 'WithdrawalOrderController@servicesOrderPassed', 'permission' => ['admin', '客服审核通过订单', '用户订单管理', 'v1']])->name('order.services.pass');
        //客服审核不通过订单
        $api->post('services/fails', ['uses' => 'WithdrawalOrderController@servicesOrderFails', 'permission' => ['admin', '客服审核不通过订单', '用户订单管理', 'v1']])->name('order.services.fails');
        //财务审核订单-锁定
        $api->post('finance/lock', ['uses' => 'WithdrawalOrderController@financeOrderLock', 'permission' => ['admin', '财务审核订单-锁定', '用户订单管理', 'v1']])->name('order.finance.lock');
        //财务审核通过订单-确定
        $api->post('finance/pass', ['uses' => 'WithdrawalOrderController@financeOrderPassed', 'permission' => ['admin', '财务审核订单-确定', '用户订单管理', 'v1']])->name('order.finance.pass');
        //财务审核不通过订单-取消
        $api->post('finance/fails', ['uses' => 'WithdrawalOrderController@financeOrderFails', 'permission' => ['admin', '财务审核订单-取消', '用户订单管理', 'v1']])->name('order.finance.fails');
        //财务审核不通过订单-拒绝
        $api->post('finance/refuse', ['uses' => 'WithdrawalOrderController@financeOrderRefuse', 'permission' => ['admin', '财务审核订单-拒绝', '用户订单管理', 'v1']])->name('order.finance.refuse');
        //财务审核不通过订单-自动打款
        $api->post('finance/automatic', ['uses' => 'WithdrawalOrderController@financeOrderAutomatic', 'permission' => ['admin', '财务审核订单-自动打款', '用户订单管理', 'v1']])->name('order.finance.automatic');
        //补单操作
        $api->post('compensateOrder', ['uses' => 'PaymentOrderController@compensateOrder', 'permission' => ['admin', '补单操作','用户订单管理', 'v1']])->name('order.compensate');
        //充值订单
        $api->post('payCompensateOrder', ['uses' => 'PaymentOrderController@compensateOrder', 'permission' => ['admin', '充值补单操作','用户订单管理', 'v1']])->name('order.pay_compensate');
        //自动出款失败取消
        $api->post('finance/autoFailed', ['uses' => 'WithdrawalOrderController@autoFailed', 'permission' => ['admin', '财务审核订单-自动打款失败', '用户订单管理', 'v1']])->name('order.finance.auto_failed');

    });

});
