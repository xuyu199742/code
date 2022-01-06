<?php

namespace Modules\Payment\Packages\ThirdPay\Lib;

use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;

class EPay extends PayAbstract
{

    const NAME = '北京E支付';
    const GATEWAY = 'https://www.hnota.com/pay/api.do';
    const CONFIG = [
        'mch_id' => '商户id',
        'mch_key' => '商户密钥',
    ];

    const APIS = [
        self::ALIPAY_QRCODE => [
            'E_ALIPAY' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'E_ALIPAYWAP' => self::NAME . '支付宝H5',
        ],
        self::WECHAT_QRCODE => [
            'E_WEIXIN' => self::NAME . '微信扫码',
        ],
        self::YUNSHANFU => [
            'E_QUICKPASS' => self::NAME . '云闪付扫码',
        ],
        self::ONLINE_BANKING => [
            'E_EXPRESS' => self::NAME . '网银快捷',
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
            $sign = $original['sign'] ?? '';
            if (!$sign && $original['orderstatus']  != 1 ) {
                \Log::channel('sifang_pay_callback')->info('E支付回调错误提示：返回' . json_encode($original));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $beforeSign = [
                'partner' => $this->sdk_config['mch_id'] ?? '',
                'ordernumber' => $original['ordernumber'],
                'orderstatus' => $original['orderstatus'],
                'paymoney' => $original['paymoney'],
            ];
            $selfSign = md5(urldecode(http_build_query($beforeSign).$key));
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('E支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $original['paymoney'] ?? 0;
            $order = PaymentOrder::where('order_no', $original['ordernumber'])->first(); //查询订单
            if (!$order) {
                \Log::channel('sifang_pay_callback')->info('E支付回调错误提示：该订单未找到，请求IP：' . getIp());
                return false;
            }
            if ($order->payment_status == PaymentOrder::SUCCESS) {
                \Log::channel('sifang_pay_callback')->info('E支付回调成功提示：该订单已支付，请求IP：' . getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $original['sysnumber']; //平台订单号
                $order->third_created_time = now();
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('E支付回调成功提示：用户金币充值成功');
                    return true;
                } else {
                    \Log::channel('sifang_pay_callback')->info('E支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('E支付回调错误提示：用户金币充值失败，金额为' . $money);
            return false;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_callback')->info('E支付回调错误提示：系统异常' . $exception->getMessage());
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
            case 'E_ALIPAY':
                $provider_key = 'ALIPAY';
                break;
            case 'E_ALIPAYWAP':
                $provider_key = 'ALIPAYWAP';
                break;
            case 'E_WEIXIN':
                $provider_key = 'WEIXIN';
                break;
            case 'E_QUICKPASS':
                $provider_key = 'QUICKPASS';
                break;
            case 'E_EXPRESS':
                $provider_key = 'EXPRESS';
                break;
            default:
                $provider_key = 'ALIPAY';
        }

        try {
            $key = $this->sdk_config['mch_key'] ?? '';
            $data = [
                'partner' => $this->sdk_config['mch_id'] ?? '',
                'banktype' => $provider_key,
                'paymoney' => $order->amount,
                'ordernumber' => $order->order_no,
                'callbackurl' => $this->sdk_config['callback'] ?? '',
            ];
            $data['sign'] = md5(urldecode(http_build_query($data).$key));
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            \Log::channel('sifang_pay_send')->info('E支付请求时间：' . $start_time . '请求数据：' . json_encode($data));
            //保存四方返回的数据
            $order->return_data = json_encode($data);
            return true;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('E支付请求异常:' . $exception->getMessage());
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
            return $this->show('FromSubmit', ['order' => $data, 'action' => self::GATEWAY, 'title' => '北京E支付', 'amount' => $data['paymoney'], 'method' => 'get']);
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('E支付请求异常:' . $exception->getMessage());
            return '系统异常';
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
        echo "ok";
        die;
    }
}
