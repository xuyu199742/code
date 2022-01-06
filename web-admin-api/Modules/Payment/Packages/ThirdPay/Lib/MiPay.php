<?php
/**
 * 小米支付sdk
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\XiaoMiSignature;

class MiPay extends PayAbstract
{
    const NAME = '小米支付';

    const CONFIG = [
        'APP_ID'     => 'APP_ID',
        'SECRET_KEY' => 'SECRET_KEY',
    ];

    const QUERY_ORDER_URL = 'http://mis.migc.xiaomi.com/api/biz/service/queryOrder.do';//查询订单接口

    const VERIFY_SESSION_URL = 'http://mis.migc.xiaomi.com/api/biz/service/verifySession.do';//验证登陆信息接口


    public static function config()
    {
        return self::CONFIG;
    }

    public static function name()
    {
        return self::NAME;
    }

    /**
     * 小米不做区分,所有返回空数组
     *
     * @return array
     */
    public static function apis()
    {
        return [];
    }

    /*
        appId	        必须	游戏ID
        cpOrderId	    必须	开发商订单ID
        cpUserInfo	    可选	开发商透传信息
        uid	            必须	用户ID
        orderId	        必须	游戏平台订单ID
        orderStatus	    必须	订单状态，TRADE_SUCCESS 代表成功
        payFee	        必须	支付金额,单位为分,即0.01 米币。
        productCode	    必须	商品代码
        productName	    必须	商品名称
        productCount	必须	商品数量
        payTime	        必须	支付时间,格式 yyyy-MM-dd HH:mm:ss
        orderConsumeType	可选	订单类型：10：普通订单11：直充直消订单
        partnerGiftConsume	可选	使用游戏券金额 （如果订单使用游戏券则有,long型），如果有则参与签名如果开发者允许使用游戏礼券则必须使用partnerGiftConsume参数，否则使用游戏礼券的消费订单会出现掉单情况。
        signature	    必须	签名,签名方法见后面说明
     */
    public function callback(): bool
    {
        $reqparams = $_REQUEST;
        \Log::channel('callback')->info('小米支付回调参数:' . json_encode($reqparams));
        $params = array();
        foreach ($reqparams as $key => $value) {
            if ($key != 'signature') {
                $params[$key] = $this->urlDecode($value);
            }
        }
        $signature = $reqparams['signature'];
        $signObj   = new XiaoMiSignature();
        if ($signObj->verifySignature($params, $signature, $this->sdk_config['SECRET_KEY'])) {
            //处理业务
            if ($reqparams['orderStatus'] == 'TRADE_SUCCESS') {
                $order = PaymentOrder::where('order_no', $reqparams['cpOrderId'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
                if ($order && $order->channelAddCoins()) {
                    $order->third_order_no = $reqparams['orderId'];
                    $order->callback_data  = json_encode($reqparams);
                    $order->save();
                }
            }
            return $this->success();
        } else {
            return $this->fail();
        }
    }

    //这边由客户端处理,直接返回
    public function send(PaymentOrder &$order): bool
    {
        return true;
    }

    public function queryOrder(PaymentOrder $order)
    {
        // TODO: Implement queryOrder() method.
    }

    //客户端处理了，这边不需要处理
    public function view(PaymentOrder $order): string
    {
        return '';
    }

    public function success()
    {
        echo '{"errcode":200}';die;
    }

    public function fail()
    {
        echo '{"errcode":1525}';die;
    }

}
