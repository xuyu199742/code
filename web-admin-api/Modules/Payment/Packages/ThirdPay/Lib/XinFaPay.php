<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 14:06
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\XinFaSignature;

class XinFaPay extends PayAbstract
{
    const NAME = '鑫发';//四方名称
    const SUCCESS = '00';//
    //商户信息配置
    const CONFIG = [
        'gateway'               => '支付网关',
        'pay_memberid'          => '商户号',
        'pay_key'               => 'MD5秘钥',
        //'3des_key'              => '3DES秘钥',
        'private_key'           => 'RSA私钥',
        //'pay_public_key'        => 'RSA支付公钥',
        //'remit_public_key'      => 'RSA代付公钥',
    ];
    //支付通道配置
    const APIS = [
       /* self::WECHAT_QRCODE => [
            'WX'       => self::NAME . '微信扫码',
        ],*/
        self::WECHAT_H5 => [
            'WX_WAP'       => self::NAME . '微信WAP',
        ],
        self::ALIPAY_QRCODE => [
            'ZFB_HB'      => self::NAME . '支付宝转卡',
        ],
        self::ALIPAY_H5 => [
            'ZFB_HB_H5'      => self::NAME . '支付宝转卡H5',
        ]
    ];

    const ERROR = [
        '00'    => '成功',
        '01'    => '失败',
        '03'    => '签名错误',
        '04'    => '其他错误',
        '05'    => '未知',
        '50'    => '网络异常',
        '99'    => '未支付',
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
            $Rsa = new XinFaSignature();
            //需要加入签名的数据
            $data['orderNo']        = $order->order_no;
            $data['version']        = 'V3.3.0.0';
            $data['charsetCode']    = 'UTF-8';
            $data['randomNum']      = (string) rand(1000,9999);
            $data['merchNo']        = $this->sdk_config['pay_memberid'] ?? '';//商户号，由平台分配;
            $data['payType']        = $order->provider->provider_key;   	//WX:微信支付,ZFB:支付宝支付
            $data['amount']         = strval($order->amount * 100);	// 单位:分
            $data['goodsName']      = '用户充值';
            $data['notifyUrl']      =  $this->sdk_config['callback'] ?? '';
            $data['notifyViewUrl']  =  $this->sdk_config['callback'] ?? '';

            //签名
            $data['sign'] = $Rsa->create_sign($data,$this->sdk_config['pay_key']);
            $json = $Rsa->json_encode_ex($data);
            $dataStr = $Rsa->encode_pay($json);
            $param = 'data=' . urlencode($dataStr) . '&merchNo=' .$this->sdk_config['pay_memberid'];

            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $Rsa->wx_post($this->sdk_config['gateway'],$param);
            $rows = $Rsa->json_to_array($result,$this->sdk_config['pay_key']);
            //dd($data,$json,$dataStr,$param,$rows);
            \Log::channel('sifang_pay_send')->info('鑫发支付请求时间：'.$start_time.'请求返回数据：'.$result);
            if ($rows['stateCode'] != '00'){
                return false;
            }
            //保持四方返回数据
            $order->return_data = $result;
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('鑫发订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            $Rsa = new XinFaSignature();
            $data = $Rsa->decode(urldecode(request('data')));//解密报文
            \Log::channel('sifang_pay_callback')->info('鑫发订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.$data);
            $rows = $Rsa->callback_to_array($data,$this->sdk_config['pay_key']);
            if ($rows['payStateCode'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('签名错误或支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $rows['orderNo'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('鑫发回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount * 100 != $rows['amount']){
                \Log::channel('sifang_pay_callback')->info('鑫发回调错误提示：充值金额不符');
                return false;
            }

            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $rows['orderNo'];//返回的流水号
            $order->success_time   = $rows['payDate'];//AA的订单时间
            $order->callback_data  = json_encode($rows, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('鑫发回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('鑫发回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        try{
            $data = json_decode($order->return_data, true);
            return redirect($data['qrcodeUrl']);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('鑫发订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}