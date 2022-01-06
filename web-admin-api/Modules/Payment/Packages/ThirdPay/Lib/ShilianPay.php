<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 17:34
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class ShiLianPay extends PayAbstract
{
    const NAME = '世联';//四方名称
    const SUCCESS = 'SUCCESS';//成功
    const FAIL = 'FAIL';//失败
    //商户信息配置
    const CONFIG = [
        'gateway'       => '支付网关',
        'mch_id'        => '商户号',
        'pay_key'       => '密钥',
    ];
    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'WXSCAN' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'WXH5'     => self::NAME . '微信h5',
        ],
        self::ALIPAY_QRCODE => [
            'ALISCAN' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'ALIH5'     => self::NAME . '支付宝h5',
        ],
        self::YUNSHANFU => [
            'CLOUDSCAN'     => self::NAME . '云闪付扫码',
            'CLOUDH5'       => self::NAME . '云闪付H5',
        ],
        self::UNIONPAY_QRCODE => [
            'UPSCAN'     => self::NAME . '银联扫码',
        ],
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
            //所有非空值字段均参与签名, 0也要参与签名
            $data = [
                'mch_id'            => $this->sdk_config['mch_id'] ?? '',//平台分配的商户号
                'trade_type'        => $order->provider->provider_key,//支付类型
                'nonce'             => str_random(20),//随机字符串，不长于 32 位
                'timestamp'         => time(),//时间戳
                'subject'           => '用户充值',//商品名称
                'out_trade_no'      => $order->order_no,//商户系统内部的订单号，32 个字符内
                'total_fee'         => $order->amount * 100,//订单金额，单位分
                'spbill_create_ip'  => getIp(),
                'notify_url'        => $this->sdk_config['callback'] ?? '',//异步地址
                'sign_type'         =>  'MD5',//签名类型，目前支持RSA和 MD5，建议使用MD5
                //'return_url'        => $this->sdk_config['callback'] ?? '',//返回地址
            ];
            //签名
            $data['sign'] = strtoupper(NormalSignature::encrypt($data, 'sign', '&key=' . $this->sdk_config['pay_key']));
            $url          = $this->sdk_config['gateway']; //接口请求地址
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            //dd($url,$data,$result,$res);
            \Log::channel('sifang_pay_send')->info('世联支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['result_code'] != self::SUCCESS){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('世联订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('世联订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //需要签名的数据
            $callback_data['result_code']       = request('result_code');//返回状态，SUCCESS 成功 FAIL 失败
            $callback_data['mch_id']            = request('mch_id');//商户号
            $callback_data['trade_type']        = request('trade_type');//交易类型
            $callback_data['out_trade_no']      = request('out_trade_no');//商户订单号(原样返回)
            $callback_data['total_fee']         = request('total_fee');//订单总金额，单位为分
            $callback_data['platform_trade_no'] = request('platform_trade_no');//平台流水号
            $callback_data['trade_no']          = request('trade_no');//平台订单号
            $callback_data['nonce']             = request('nonce');//随机字符串
            $callback_data['pay_time']          = request('pay_time');//订单时间
            $callback_data['timestamp']         = request('timestamp');//时间戳
            $callback_data['sign_type']         = request('sign_type');//签名类型，如果有的时候需要加入签名
            //签名
            $sign                               = request('sign');
            //验签
            ksort($callback_data);
            reset($callback_data);
            $md5str = "";
            foreach ($callback_data as $key => $val) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
            $selfsign = strtoupper(md5($md5str . "key=" . $this->sdk_config['pay_key']));
            if ($selfsign != $sign){
                \Log::channel('sifang_pay_callback')->info('世联回调错误提示：签名有误,内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['result_code'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('世联回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['out_trade_no'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('世联回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount * 100 != $callback_data['total_fee']){
                \Log::channel('sifang_pay_callback')->info('世联回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['trade_no'];//平台订单号
            $order->success_time   = date('Y-m-d H:i:s', $callback_data['timestamp'] ?? time());
            $order->callback_data   = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('世联回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('世联回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        //解析接口返回的数据
        $return_data = json_decode($order->return_data, true);
        //jump=1 表示要跳转到指定链接
        if (isset($return_data['jump'])  == 1) {
            return redirect($return_data['pay_url']);
        }
        //jump=0||“”，则链接可以直接拿来生成二维码
        return $this->show('ShilianPay', ['return_data'=>$return_data]);
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}
