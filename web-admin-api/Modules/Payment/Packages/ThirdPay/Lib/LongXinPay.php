<?php

namespace Modules\Payment\Packages\ThirdPay\Lib;

use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
class LongXinPay extends PayAbstract
{

    const NAME = '龙鑫';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id' => '商户id',
        'mch_key' => '商户密钥',
    ];

    const CHANNEL_MAPPING = [
        'lx_ali_dpc' => 'ali_dpc',
        'lx_ali_dwap' => 'ali_dwap',
        'lx_weixin' => 'weixin',
        'lx_wxh5' => 'wxh5',
        'lx_ysf' => 'ysf',
    ];

    const APIS = [
        self::ALIPAY_QRCODE => [
            'lx_ali_dpc' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'lx_ali_dwap' => self::NAME . '支付宝H5',
        ],
        self::WECHAT_QRCODE => [
            'lx_weixin' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'lx_wxh5' => self::NAME . '微信H5',
        ],
        self::YUNSHANFU => [
            'lx_ysf' => self::NAME . '云闪付',
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
            $callback_data = json_decode($callback_data['postdata'] ?? "[]",true);
            $sign = $callback_data['sign'] ?? null;
            if (!$sign) {
                \Log::channel('sifang_pay_callback')->info('龙鑫支付回调错误提示：返回' . json_encode($callback_data));
                return false;
            }
            $sign_data = [
                'customerid' => $callback_data['customerid'],
                'status' => $callback_data['status'],
                'sdpayno' => $callback_data['sdpayno'],
                'sdorderno' => $callback_data['sdorderno'],
                'total_fee' => $callback_data['total_fee'],
                'paytype' => $callback_data['paytype'],
            ];
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = md5(http_build_query($sign_data) . "&{$key}"); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('龙鑫支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode(request()->all()));
                return false;
            }
            $money = $callback_data['total_fee'];

            $order = PaymentOrder::where('order_no', $callback_data['sdorderno'])->first(); //查询订单
            if (!$order) {
                \Log::channel('sifang_pay_callback')->info('龙鑫支付回调错误提示：该订单未找到，请求IP：' . getIp());
                return false;
            }
            if ($order->payment_status == PaymentOrder::SUCCESS) {
                \Log::channel('sifang_pay_callback')->info('龙鑫支付回调成功提示：该订单已支付，请求IP：' . getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $callback_data['sdpayno']; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode(request()->all(), true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('龙鑫支付回调成功提示：用户金币充值成功');
                    return true;
                } else {
                    \Log::channel('sifang_pay_callback')->info('龙鑫支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('龙鑫支付回调错误提示：用户金币充值失败，金额为' . $money);
            return false;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_callback')->info('龙鑫支付回调错误提示：系统异常' . $exception->getMessage());
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
                'version' => '1.0',
                'customerid' => (int)$this->sdk_config['mch_id'] ?? '',
                'total_fee' => number_format(floatval($order->amount), 2, '.', ''),//订单金额
                'sdorderno' => $order->order_no,
                'notifyurl' => $this->sdk_config['callback'] ?? '',
                'returnurl' => $this->sdk_config['callback'] ?? '',
            ];
            $data['sign'] = md5(urldecode(http_build_query($data) . "&{$key}")); // 生成签名
            $data['paytype'] = self::CHANNEL_MAPPING[$order->provider->provider_key ?? ''] ?? '';
            $data['pay_model'] = 2;
            $start_time = date('Y-m-d H:i:s', time());//请求时间
            $end_time = date('Y-m-d H:i:s', time());//相应时间
            \Log::channel('sifang_pay_send')->info('龙鑫支付请求时间：' . $start_time . '响应时间：' . $end_time . '请求地址：' . $url . '请求数据：' . json_encode($data));
            //保存四方返回的数据
            $order->return_data = json_encode($data);
            return true;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('龙鑫支付请求异常:' . $exception->getMessage());
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
            $data = json_decode($order->return_data, true);
            $url = $this->sdk_config['gateway']; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('FromSubmit', ['order' => $data, 'action' => $url, 'title' => '龙鑫支付', 'amount' => $data['total_fee']]);
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('龙鑫付请求异常:' . $exception->getMessage());
            return '系统异常';
        }
    }


    /**
     * 查询订单
     * @param PaymentOrder $order
     *
     * @return mixed
     */
    public
    function queryOrder(PaymentOrder $order)
    {
        return '';
    }

    public
    function success()
    {
        echo 'success';
        die;
    }
}
