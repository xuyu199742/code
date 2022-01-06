<?php


$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    //登陆路由
    $api->group(['prefix' => 'payment', 'namespace' => '\Modules\Payment\Http\Controllers'], function ($api) {
        //充值方式和充值通道列表
        $api->get('/paymentsList/{game_id}', 'PaymentController@paymentsList');
        //第三方充值类型
        $api->get('/types', 'PaymentController@types');
        //充值下单
        $api->post('/pay', 'PaymentController@pay');
        //渠道下单
        $api->post('/pay/{channel}', 'PaymentController@ChannelPay');

        //充值页面
        $api->get('/order/{order_no}', 'PaymentController@order');
        //充值回调
        $api->any('/callback/{type}', 'PaymentController@callback');

        //代付回调
        $api->any('remit/{type}', 'RemitController@callback');

        //四方测试请求回调
        $api->any('/sf_test', 'PaymentController@sf_test_callback');
    });
});
