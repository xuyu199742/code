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

class GuangXin extends PayAbstract
{
    const NAME = '光信';//四方名称
    const SUCCESS = '00';//四方支付成功异步返回标识
    //商户信息配置
    const CONFIG = [
        'gateway'      => '支付网关',
        'pay_memberid' => '商户号',
        'pay_key'      => '密钥',
    ];
    //支付通道配置
    const APIS = [
        self::ALIPAY_QRCODE => [
            'GX_ZFB_QRCODE' => self::NAME . '支付宝扫码',
        ],
        self::WECHAT_QRCODE => [
            'GX_WX_QRCODE'       => self::NAME . '微信扫码',
        ],
        self::ALIPAY_H5 => [
            'GX_ZFB_H5'       => self::NAME . '支付宝H5',
        ],
        self::WECHAT_H5 => [
            'GX_WX_H5'      => self::NAME . '微信H5',
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
            //通道转换
            switch ($order->provider->provider_key){
                case 'GX_ZFB_QRCODE':
                    $provider_key  = 1;
                    break;
                case 'GX_WX_QRCODE':
                    $provider_key  = 2;
                    break;
                case 'GX_ZFB_H5':
                    $provider_key  = 3;
                    break;
                case 'GX_WX_H5':
                    $provider_key  = 4;
                    break;
                default:
                    $provider_key = 1;
            }
            //需要加入签名的数据
            $data = [
                'mchNo'      => $this->sdk_config['pay_memberid'] ?? '',//商户唯一标识，注册后获得
                'mchUserNo'  => $order->user_id,
                'outTradeNo' => $order->order_no,
                'channel'    => $provider_key,
                'amount'     => (double)$order->amount,//单位元
                'body'       => '用户充值',
                'payDate'    => date('YmdHis'),
                'notifyUrl'  => $this->sdk_config['callback'] ?? '',//支付回调
                'returnUrl'  => $this->sdk_config['callback'] ?? '',//支付跳转
            ];
            //签名
            $data['sign'] = NormalSignature::signature($data,'&signKey='.$this->sdk_config['pay_key'],false);
            //保持四方请求数据，然后用于表单方式提交
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('光信订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('光信订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //获取需要加入签名的数据
            $callback_data['resultCode'] = request('resultCode');
            $callback_data['resultMsg']  = request('resultMsg');
            $callback_data['outTradeNo'] = request('outTradeNo');//商户系统内部订单号
            $callback_data['amount']     = request('amount');
            $callback_data['returnCode'] = request('returnCode');
            //对应的签名
            $sign                        = request('sign');
            //签名
            $selfsign  = NormalSignature::signature($callback_data,'&signKey='.$this->sdk_config['pay_key'],false);
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('光信回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['resultCode'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('光信回调错误提示：支付失败');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['outTradeNo'])->first();
            if (!$order){
                if ($order->payment_status == PaymentOrder::SUCCESS){
                    return true;
                }
                \Log::channel('sifang_pay_callback')->info('光信回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount != $callback_data['amount']){
                \Log::channel('sifang_pay_callback')->info('光信回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['outTradeNo'];
            $order->success_time   = date('Y-m-d H:i:s',time());
            $order->callback_data  = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('光信回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('光信回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        try{
            $data = json_decode($order->return_data, true);
            $url          = $this->sdk_config['gateway']; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('GuangxinPay', ['order'=>$data, 'action'=>$url]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('光信订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }

}