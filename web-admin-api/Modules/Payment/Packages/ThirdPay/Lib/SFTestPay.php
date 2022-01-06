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

class SFTestPay extends PayAbstract
{

    const NAME = '四方测试支付';

    const CONFIG = [
        'gateway' => '支付网关',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'SIFANG_WX_H5' => self::NAME . '微信H5',
        ],
        self::WECHAT_QRCODE => [
            'SIFANG_WX_QRCODE' => self::NAME . '微信扫码',
        ],
        self::ALIPAY_H5 => [
            'SIFANG_ALIPAY_H5'  => self::NAME . '支付宝H5',
        ],
        self::ALIPAY_QRCODE => [
            'SIFANG_ALIPAY_QRCODE'  => self::NAME . '支付宝扫码',
        ],
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
            $status = $original['status'] ?? '';
            if(!$status){
                \Log::channel('sifang_pay_callback')->info('四方测试支付回调错误：'.getIp());
                return false;
            }
            $order = PaymentOrder::where('order_no',$callback_data['order_no'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('四方测试支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('四方测试支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($status == 'success') {
                $order->third_order_no = $original['third_order_no'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('四方测试支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('四方测试支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('四方测试支付回调错误提示：用户金币充值失败，回调状态为'.$status);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('四方测试支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = 'success';//成功

    /**
     * 请求四方订单方法
     *
     * @param PaymentOrder $order
     *
     * @return bool
     */
    public function send(PaymentOrder &$order): bool
    {
        try{
            //整理订单数据
            $data         = [
                'notify_url'   => $this->sdk_config['callback'] ?? '',
                'order_no'     => $order->order_no,
                'ip'           => getIp(),
                'amount'       => $order->amount, //订单金额
                'gateway'      => $order->provider->provider_key,
            ];
            //保存四方返回的数据
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('四方测试支付请求异常:'.$exception->getMessage());
            return false;
        }
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
            $url = $this->sdk_config['gateway']; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('FromSubmit', ['order'=>$data, 'action'=>$url,'title' => '四方测试支付','amount' => $data['amount']]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('四方测试支付请求异常:'.$exception->getMessage());
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
        echo 'success';
        die;
    }



}