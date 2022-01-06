<?php

namespace Modules\Payment\Packages\ThirdPay\Lib;

use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class JinFuPay extends PayAbstract
{

    const NAME = '金富支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id' => '商户id',
        'mch_key' => '商户密钥',
    ];

    const APIS = [
        self::ALIPAY_QRCODE => [
            'ALIPAY_NATIVE' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'ALIPAY_H5' => self::NAME . '支付宝H5',
            'ALIPAY_XE' => self::NAME . '支付宝小额',
        ],
        self::WECHAT_QRCODE => [
            'WEIXIN_NATIVE' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'WEIXIN_H5' => self::NAME . '微信H5',
        ],
        self::UNIONPAY_QRCODE => [
            'GATEWAY_QUICK' => self::NAME . '银联快捷',
        ],
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
            $sign = $callback_data['sign'];
            $sign_data = [
                'out_trade_no' => $callback_data['out_trade_no'],
                'trade_no' => $callback_data['trade_no'],
                'total_amount' => $callback_data['total_amount'],
                'trade_status' => $callback_data['trade_status'],
            ];
            if (!$sign) {
                \Log::channel('sifang_pay_callback')->info('金富支付回调错误提示：返回' . json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = md5(http_build_query($sign_data) . "{$key}"); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('金富支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['total_amount'] / 100;

            $order = PaymentOrder::where('order_no', $callback_data['out_trade_no'])->first(); //查询订单
            if (!$order) {
                \Log::channel('sifang_pay_callback')->info('金富支付回调错误提示：该订单未找到，请求IP：' . getIp());
                return false;
            }
            if ($order->payment_status == PaymentOrder::SUCCESS) {
                \Log::channel('sifang_pay_callback')->info('金富支付回调成功提示：该订单已支付，请求IP：' . getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $callback_data['trade_no']; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('金富支付回调成功提示：用户金币充值成功');
                    return true;
                } else {
                    \Log::channel('sifang_pay_callback')->info('金富支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('金富支付回调错误提示：用户金币充值失败，金额为' . $money);
            return false;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_callback')->info('金富支付回调错误提示：系统异常' . $exception->getMessage());
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

    const SUCCESS = '0';//成功

    /**
     * 请求四方订单方法
     *
     * @param PaymentOrder $order
     *
     * @return bool
     */
    public function send(PaymentOrder &$order): bool
    {
        try {
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $key = $this->sdk_config['mch_key'] ?? '';

            $data = [
                'app_id' => $this->sdk_config['mch_id'] ?? '',
                'interface_version' => 'V3.1',
                'notify_url' => $this->sdk_config['callback'] ?? '',
                'out_trade_no' => $order->order_no,
                'total_amount' => $order->amount * 100, //订单金额
                'trade_type' => $order->provider->provider_key ?? '',
            ];
            $data['sign'] = md5(urldecode(http_build_query($data) . "{$key}")); // 生成签名
            $data['client_ip'] = getIp();
            $start_time = date('Y-m-d H:i:s', time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $end_time = date('Y-m-d H:i:s', time());//相应时间
            \Log::channel('sifang_pay_send')->info('金富支付请求时间：' . $start_time . '响应时间：' . $end_time . '请求地址：' . $url . '请求数据：' . json_encode($data) . '请求返回数据：' . $result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200) {
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('金富支付请求异常:' . $exception->getMessage());
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
        return $order->return_data;
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
