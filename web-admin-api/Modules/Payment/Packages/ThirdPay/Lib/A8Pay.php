<?php

namespace Modules\Payment\Packages\ThirdPay\Lib;

use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;

class A8Pay extends PayAbstract
{

    const NAME = 'A8支付';
    const GATEWAY = 'https://p.8868.nl/api/v2/gateway';
    const CONFIG = [
        'mch_id' => '商户id',
        'mch_key' => '商户密钥',
    ];

    const APIS = [
//        self::ALIPAY_H5 => [
//            'A8_ALIPAY_H5' => self::NAME . '支付宝H5',
//        ],
//        self::WECHAT_QRCODE => [
//            'A8_WECHAT_QRCODE' => self::NAME . '微信扫码',
//        ],
        self::WECHAT_H5 => [
            'A8_WECHAT_H5' => self::NAME . '微信H5',
        ],
//        self::YUNSHANFU => [
//            'A8_YUNSHANFU' => self::NAME . '云闪付',
//        ],
//        self::UNIONPAY_QRCODE => [
//            'A8_UNIONPAY_QRCODE' => self::NAME . '快捷支付',
//        ],
    ];

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try {
            $callback_data = request()->all();
            $original = $callback_data;
            $sign = $original['sign'] ?? '';
            if (!$sign && $original['order_state']  != 82002 ) {
                \Log::channel('sifang_pay_callback')->info('A8支付回调错误提示：返回' . json_encode($original));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $beforeSign = [
                'merchant_no' => $original['merchant_no'],
                'order_no' => $original['order_no'],
                'platform_order_no' => $original['platform_order_no'],
                'order_money' => $original['order_money'],
                'pay_time' => $original['pay_time'],
                'pay_money' => $original['pay_money'],
                'order_state' => $original['order_state'],
            ];

            ksort($beforeSign);
            $selfSign = strtoupper(md5($this->getSingString($beforeSign, $key)));
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('A8支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $original['pay_money'] / 100;
            $order = PaymentOrder::where('order_no', $original['order_no'])->first(); //查询订单
            if (!$order) {
                \Log::channel('sifang_pay_callback')->info('A8支付回调错误提示：该订单未找到，请求IP：' . getIp());
                return false;
            }
            if ($order->payment_status == PaymentOrder::SUCCESS) {
                \Log::channel('sifang_pay_callback')->info('A8支付回调成功提示：该订单已支付，请求IP：' . getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $original['platform_order_no']; //平台订单号
                $order->third_created_time = now();
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('A8支付回调成功提示：用户金币充值成功');
                    return true;
                } else {
                    \Log::channel('sifang_pay_callback')->info('A8支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('A8支付回调错误提示：用户金币充值失败，金额为' . $money);
            return false;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_callback')->info('A8支付回调错误提示：系统异常' . $exception->getMessage());
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
        switch ($order->provider->provider_key) {
            case 'A8_ALIPAY_H5':
                $provider_key = '40002';
                break;
            case 'A8_WECHAT_QRCODE':
                $provider_key = '40003';
                break;
            case 'A8_WECHAT_H5':
                $provider_key = '40004';
                break;
            case 'A8_YUNSHANFU':
                $provider_key = '40006';
                break;
            case 'A8_UNIONPAY_QRCODE':
                $provider_key = '40007';
                break;
            default:
                $provider_key = '40004';
        }

        try {
            $key = $this->sdk_config['mch_key'] ?? '';
            $data = [
                'order_no' => $order->order_no,
                'async_url' => $this->sdk_config['callback'],
                'extend' => '扩展字段',
                'sync_url' => $this->sdk_config['callback'],
                'channel' => $provider_key,
                'order_money' => $order->amount * 100,
            ];
            $requestData = [
                'businessType' => 'order',
                'data' => base64_encode(json_encode($data, JSON_UNESCAPED_SLASHES)),
                'ipAddr' => request()->ip(),
                'merchantNo' =>  $this->sdk_config['mch_id'] ?? '',
                'timeStamp' => $this->getMillisecond(),
            ];
            ksort($requestData);
            $requestData['sign'] = strtoupper(md5($this->getSingString($requestData, $key)));
            $start_time = now()->toDateTimeString();
            $result = $this->post(self::GATEWAY, $requestData); //发送请求
            $end_time = now()->toDateTimeString();
            $res = json_decode($result['res'], true);
            \Log::channel('sifang_pay_send')->info('A8支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.self::GATEWAY.'请求数据：'.json_encode($requestData).'请求返回数据：'.json_encode($result));
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['success'] != true){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('A8支付请求异常:' . $exception->getMessage());
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
        try {
            $return_data = json_decode($order->return_data, true);
            $data = json_decode(base64_decode($return_data['data']), true);
            return redirect($data['payUrl']);
        } catch (\Exception $e) {
            return "";
            \Log::channel('sifang_pay_send')->info('A8支付请求异常:' . $e->getMessage());
        }
    }

    public function sdk(PaymentOrder $order)
    {
        $return_data = json_decode($order->return_data, true);
        return $return_data['result']['url'];
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
        echo "success";
        die;
    }

    public function getSingString($data, $key_)
    {
        $sing = "";
        foreach ($data as $key => $param) {
            $sing .= $key.'=' . $param . '&';
        }
        $sing = $sing.'key=' . $key_;

        return $sing;
    }

    public function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return $t2 . ceil( ($t1 * 1000) );
    }
}
