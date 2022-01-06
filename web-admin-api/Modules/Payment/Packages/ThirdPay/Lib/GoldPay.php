<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/9
 * Time: 9:58
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class GoldPay extends PayAbstract
{

    const NAME = '金支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户APPID',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'Gold_WX_H5' => self::NAME . '微信H5',
        ],
        self::WECHAT_QRCODE => [
            'Gold_WX_QRCODE' => self::NAME . '微信扫码',
        ],
        self::ALIPAY_H5 => [
            'Gold_ZFB_H5' => self::NAME . '支付宝H5',
        ],
        self::ALIPAY_QRCODE => [
            'Gold_ZFB_QRCODE' => self::NAME . '支付宝扫码',
        ],
        self::YUNSHANFU => [
            'Gold_YUNSHANFU'     => self::NAME . '云闪付',
        ],
        self::UNIONPAY_QRCODE => [
            'Gold_UNION_QRCODE'     => self::NAME . '银联扫码',
        ]
    ];

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            $callback_data = request()->all();
            $original = $callback_data;
            $sign = $callback_data['sign'] ?? '';
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('金支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = strtoupper(md5('amount^'.$callback_data['amount'].'&datetime^'.$callback_data['datetime'].'&memberid^'.$callback_data['memberid'].'&orderid^'.$callback_data['orderid'].'&returncode^'.$callback_data['returncode'].'&key='.$key)); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('金支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['amount'];
            $returncode = $callback_data['returncode'];
            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money && $returncode == self::SUCCESS) {
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('金支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('金支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('金支付回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

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

    const SUCCESS = '2';//成功

    /**
     * 请求四方订单方法
     *
     * @param PaymentOrder $order
     *
     * @return bool
     */
    public function send(PaymentOrder &$order): bool
    {
        //通道转换
        switch ($order->provider->provider_key){
            case 'Gold_WX_H5':
                $provider_key  = 'WECHAT_WAP';
                break;
            case 'Gold_WX_QRCODE':
                $provider_key  = 'WECHAT';
                break;
            case 'Gold_ZFB_H5':
                $provider_key  = 'ALIPAY_WAP';
                break;
            case 'Gold_ZFB_QRCODE':
                $provider_key  = 'ALIPAY';
                break;
            case 'Gold_YUNSHANFU':
                $provider_key  = 'YL_CP';
                break;
            case 'Gold_UNION_QRCODE':
                $provider_key  = 'YL';
                break;
            default:
                $provider_key = 'WECHAT';
        }
        $key = $this->sdk_config['mch_key'] ?? '';
        //整理订单数据
        $data         = [
            'pay_memberid'   => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'pay_orderid'    => $order->order_no,
            'pay_amount'     => $order->amount,//订单金额
            'pay_applydate'  => date('YmdHis'),
            'pay_channelCode'=> $provider_key,
            'pay_notifyurl'  => $this->sdk_config['callback'] ?? ''
        ];
        //保存四方返回的数据
        $order->return_data = json_encode($data);
        return true;
    }

    /**
     * 处理订单页面
     *
     * @param PaymentOrder $order
     *
     * @return mixed
     */
    public function view(PaymentOrder $order): string
    {
        try{
            $data = json_decode($order->return_data, true);
            $url  = $this->sdk_config['gateway']; //拼装请求地址
            $key = $this->sdk_config['mch_key'] ?? '';
            //form方式提交，由客户端发送请求
            return $this->show('GoldPay', ['order'=>$data, 'action'=>$url,'key' => $key]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('金支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    /**
     * 查询订单
     * @param PaymentOrder $order
     *
     * @return mixed
     */
    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }

    public function success()
    {
        echo 'SUCCESS';
        die;
    }
}