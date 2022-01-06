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

class YiJiaPay extends PayAbstract
{
    const NAME = '一加';//四方名称
    //商户信息配置
    const CONFIG = [
        'gateway'      => '支付网关',
        'pay_memberid' => '商户号',
        'pay_key'      => '密钥',
    ];
    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'wxqrcode'       => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'wxwap'       => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'qrcode'      => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'wap'      => self::NAME . '支付宝H5',
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
                'merchant'      => $this->sdk_config['pay_memberid'] ?? '',//商户号，由平台分配
                'amount'        => intval($order->amount * 100),//订单金额，以分为单位，如1元传为100
                'pay_type'      => $order->provider->provider_key,//支付产品类型
                'order_no'      => $order->order_no,//商户订单号
                'order_time'    => time(),//下单时间，Unix时间戳秒
                'subject'       => '用户充值',//异步通知接口地址，不填则不通知
                'notify_url'    => $this->sdk_config['callback'] ?? '',//异步回调地址
                'callback_url'  => $this->sdk_config['callback'] ?? '',//同步回调地址
            ];
            //签名，签名字段需要小写
            ksort($data);//对需要签名的数据进行排序
            $md5str = '';
            foreach ($data as $key => $val) {
                $md5str = $md5str . $key .'=' . $val . '&';
            }
            $data['sign'] = Sha1($md5str . 'key=' . $this->sdk_config['pay_key']);
            $url          = $this->sdk_config['gateway']; //接口请求地址
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $res = json_decode($result['res'], true); //解析返回结果
            //dd($url,$data,$result,$res);
            \Log::channel('sifang_pay_send')->info('一加支付请求时间：'.$start_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功或下单是否成功
            if ($res['code'] != '0000' || $res['success'] != true){
                return false;
            }
            //保持四方返回数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('一加订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('一加订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //获取需要加入签名的数据
            $callback_data['merchant']      = request('merchant');//商户编号
            $callback_data['tranNo']        = request('tranNo');//平台订单号
            $callback_data['channelType']   = request('channelType');//“ 7 ”  :  ”支付宝H5”
            $callback_data['orderId']       = request('orderId');//商户订单号
            $callback_data['amount']        = request('amount');//单位元
            //对应的签名
            $sign                           = request('sign');//大写sha1签名值
            //签名
            ksort($callback_data);//对需要签名的数据进行排序
            $md5str = '';
            foreach ($callback_data as $key => $val) {
                $md5str = $md5str . $key .'=' . $val . '&';
            }
            $selfsign = strtoupper(Sha1($md5str . 'key=' . $this->sdk_config['pay_key']));
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('一加回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['orderId'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('一加回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount != $callback_data['amount']){
                \Log::channel('sifang_pay_callback')->info('一加回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['tranNo'];//一加返回的流水号
            $order->success_time   = date('Y-m-d H:i:s',time());//一加的订单时间
            $order->callback_data  = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('一加回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('一加回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        try{
            $data = json_decode($order->return_data, true);
            return redirect($data['result']['qrCode']);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('一加订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}