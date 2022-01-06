<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 14:06
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class DingDingPay extends PayAbstract
{
    const NAME = '钉钉';//四方名称
    const SUCCESS = 3;//1、预下单成功2、预下单失败3、交易成功4、交易失败5、交易超时6、交易中
    //商户信息配置
    const CONFIG = [
        'gateway'      => '支付网关',
        'pay_memberid' => '商户号',
        'pay_key'      => '密钥',
    ];
    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'wxPaySM'       => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'wxPayH5'       => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'aliPaySM'      => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'aliPayH5'      => self::NAME . '支付宝H5',
        ]
    ]; 

    public static function config()
    {
        return self::CONFIG;
    }

    public static function name()
    {
        return self::NAME;
    }

    public static function apis()
    {
        return self::APIS;
    }

    //表单提交，即收银台模式
    public function send(PaymentOrder &$order): bool
    {
        try{
            //需要加入签名的数据
            $data = [
                'version'       => 'v1.0',//当前：v1.0
                'type'          => $order->provider->provider_key,//详见文末：接口类型表
                'userId'        => $this->sdk_config['pay_memberid'] ?? '',//我方平台分配的唯一标识
                'requestNo'     => $order->order_no,//你提交的流水号。此参数不能重复，最长32位
                'amount'        => $order->amount * 100,//订单金额，以分为单位，如1元传为100
                'callBackURL'   => $this->sdk_config['callback'] ?? '',//异步通知接口地址，不填则不通知
                'redirectUrl'   => $this->sdk_config['callback'] ?? '',//支付完成后跳转地址，补充：此项不一定支持跳转
            ];
            //签名，签名字段需要小写
            $data['sign'] = NormalSignature::signature($data,'&key='.$this->sdk_config['pay_key'],false);
            $url          = $this->sdk_config['gateway']; //接口请求地址
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $paramstr = json_encode($data);
            $opts = array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    'content' => $paramstr
                )
            );
            $context  = stream_context_create($opts);
            $result = file_get_contents($url, false, $context);// $result 为返回结果
            \Log::channel('sifang_pay_send')->info('钉钉支付请求时间：'.$start_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result);

            //保持四返回
            $order->return_data = $result ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('钉钉订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('钉钉订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //获取需要加入签名的数据
            $callback_data['userId']        = request('userId');//商户号
            $callback_data['status']        = request('status');//1、预下单成功2、预下单失败3、交易成功4、交易失败5、交易超时6、交易中
            $callback_data['message']       = request('message');//成功则返”000000”,失败返回原因
            $callback_data['amount']        = request('amount');//订单金额，以分为单位，如1元表示为100
            $callback_data['payAmount']     = request('payAmount');//实际支付金额，以分为单位，如1元表示为100
            $callback_data['requestNo']     = request('requestNo');//你提交的流水号。此参数不能重复，最长32位
            $callback_data['orderNo']       = request('orderNo');//我方系统生成的流水号。此参数不重复，用于异步查询使用
            $callback_data['payTime']       = request('payTime');//用户实际支付时间，格式为yyyy-MM-dd HH:mm:ss
            //对应的签名
            $sign                           = request('sign');
            //签名
            $selfsign = NormalSignature::signature($callback_data,'&key='.$this->sdk_config['pay_key'],false);
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('钉钉回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['status'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('钉钉回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['requestNo'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('钉钉回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount * 100 != $callback_data['amount']){
                \Log::channel('sifang_pay_callback')->info('钉钉回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['orderNo'];//钉钉返回的流水号
            $order->success_time   = $callback_data['payTime'];//钉钉的订单时间
            $order->callback_data  = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('钉钉回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('钉钉回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        try{
            $data = json_decode($order->return_data, true);
            //url跳转
            if (isset($data['payUrl']) && !empty($data['payUrl'])){
                return redirect($data['payUrl']);
            }
            //html页面输出
            if (isset($data['payHTML']) && !empty($data['payHTML'])){
                return $data['payHTML'];
            }
            return $data['message'];
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('钉钉订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}