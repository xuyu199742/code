<?php

namespace Modules\Payment\Packages\ThirdPay\Lib;

use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class KaNiuPay extends PayAbstract
{

    const NAME = '卡牛财付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id' => '商户id',
        'mch_key' => '商户密钥',
    ];

    const APIS = [
        self::ALIPAY_QRCODE => [
            'KA_NIU_ALIPAY_QRCODE' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'KA_NIU_ALIPAY_H5' => self::NAME . '支付宝H5',
        ],
        self::WECHAT_QRCODE => [
            'KA_NIU_WECHAT_QRCODE' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'KA_NIU_WECHAT_H5' => self::NAME . '微信H5',
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
            $sign = $callback_data['sign'] ?? '';
            $sign_data = [
                "memberid" => $callback_data["memberid"] ?? '', // 商户ID
                "orderid" =>  $callback_data["orderid"] ?? '', // 订单号
                "amount" =>  $callback_data["amount"] ?? '', // 交易金额
                "datetime" =>  $callback_data["datetime"] ?? '', // 交易时间
                "transaction_id" =>  $callback_data["transaction_id"] ?? '', // 支付流水号
                "returncode" => $callback_data["returncode"] ?? '',
            ];
            ksort($sign_data);
            reset($sign_data);
            if (!$sign && $sign_data['returncode']  != '00' ) {
                \Log::channel('sifang_pay_callback')->info('卡牛财付回调错误提示：返回' . json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = strtoupper(md5(urldecode(http_build_query($sign_data). "&key={$key}")));
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('卡牛财付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['amount'];

            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->first(); //查询订单
            if (!$order) {
                \Log::channel('sifang_pay_callback')->info('卡牛财付回调错误提示：该订单未找到，请求IP：' . getIp());
                return false;
            }
            if ($order->payment_status == PaymentOrder::SUCCESS) {
                \Log::channel('sifang_pay_callback')->info('卡牛财付回调成功提示：该订单已支付，请求IP：' . getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $callback_data['transaction_id']; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('卡牛财付回调成功提示：用户金币充值成功');
                    return true;
                } else {
                    \Log::channel('sifang_pay_callback')->info('卡牛财付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('卡牛财付回调错误提示：用户金币充值失败，金额为' . $money);
            return false;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_callback')->info('卡牛财付回调错误提示：系统异常' . $exception->getMessage());
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
            case 'KA_NIU_ALIPAY_QRCODE':
                $provider_key = '903';
                break;
            case 'KA_NIU_ALIPAY_H5':
                $provider_key = '904';
                break;
            case 'KA_NIU_WECHAT_QRCODE':
                $provider_key = '902';
                break;
            case 'KA_NIU_WECHAT_H5':
                $provider_key = '917';
                break;
            default:
                $provider_key = '903';
        }

        try {
            $key = $this->sdk_config['mch_key'] ?? '';
            $data = [
                'pay_memberid' => (int)$this->sdk_config['mch_id'] ?? '',
                'pay_orderid' => $order->order_no,
                'pay_amount' => number_format(floatval($order->amount), 2, '.', ''),//订单金额
                "pay_applydate" =>  date("Y-m-d H:i:s"),
                'pay_bankcode' => $provider_key,
                'pay_notifyurl' => $this->sdk_config['callback'] ?? '',
                'pay_callbackurl' => $this->sdk_config['callback'] ?? '',
            ];
            ksort($data);
            $sign = strtoupper(md5(urldecode(http_build_query($data). "&key={$key}")));
            $data["pay_md5sign"] = $sign;
            $data["pay_productname"] = "团购商品";
            $start_time = date('Y-m-d H:i:s', time());//请求时间
            \Log::channel('sifang_pay_send')->info('卡牛财富请求时间：' . $start_time . '请求数据：' . json_encode($data));
            $order->return_data = json_encode($data);
            return true;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('卡牛财富请求异常:' . $exception->getMessage());
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
            return $this->show('FromSubmit', ['order' => $data, 'action' => $url, 'title' => '卡牛财富', 'amount' => $data['pay_amount']]);
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('卡牛财富请求异常:' . $exception->getMessage());
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
        echo "OK";
        die;
    }
}
