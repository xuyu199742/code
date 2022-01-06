<?php

namespace Modules\Payment\Packages\ThirdPay\Lib;

use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class ShenSuPay extends PayAbstract
{

    const NAME = '神速支付';
    const GATEWAY = 'https://uthasbfaow.hgqihui.com/api/order/create';
    const CONFIG = [
        'mch_id' => '商户id',
        'mch_key' => '商户密钥',
    ];

    const APIS = [
        self::ALIPAY_QRCODE => [
            'SHEN_SU_ALIPAY_QRCODE' => self::NAME . '支付宝扫码',
            'SHEN_SU_ALIPAY_ZZ' => self::NAME . '支付宝转账',
        ],
        self::ALIPAY_H5 => [
            'SHEN_SU_ALIPAY_H5' => self::NAME . '支付宝H5',
            'SHEN_SU_ALIPAY_HF' => self::NAME . '支付宝话费',
            'SHEN_SU_ALIPAY_ZK' => self::NAME . '支付宝转卡',
        ],
        self::WECHAT_H5 => [
            'SHEN_SU_WECHAT_H5' => self::NAME . '微信H5',
            'SHEN_SU_WECHAT_HF' => self::NAME . '微信话费',
        ],
        self::YUNSHANFU => [
            'SHEN_SU_YUNSHANFU' => self::NAME . '云闪付转卡',
        ],
        self::UNIONPAY_QRCODE => [
            'SHEN_SU_UNIONPAY' => self::NAME . '银行卡转卡',
        ],
        self::ALIPAY_SDK=>[
            'SHEN_SU_ALIPAY_H5_SDK' => self::NAME . '支付宝原生APP',
            'SHEN_SU_ALIPAY_HF_SDK' => self::NAME . '支付宝原生APP(话费)',
        ]
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
            if (!$sign && $original['status']  != 'SUCCESS' ) {
                \Log::channel('sifang_pay_callback')->info('神速支付回调错误提示：返回' . json_encode($original));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = strtoupper(NormalSignature::encrypt($original,'sign','&key='.$key));
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('神速支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $original['amount'] ?? 0;
            $order = PaymentOrder::where('order_no', $original['outOrderNo'])->first(); //查询订单
            if (!$order) {
                \Log::channel('sifang_pay_callback')->info('神速支付回调错误提示：该订单未找到，请求IP：' . getIp());
                return false;
            }
            if ($order->payment_status == PaymentOrder::SUCCESS) {
                \Log::channel('sifang_pay_callback')->info('神速支付回调成功提示：该订单已支付，请求IP：' . getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $original['sysOrderNo']; //平台订单号
                $order->third_created_time = $original['payTime'];
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('神速支付回调成功提示：用户金币充值成功');
                    return true;
                } else {
                    \Log::channel('sifang_pay_callback')->info('神速支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('神速支付回调错误提示：用户金币充值失败，金额为' . $money);
            return false;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_callback')->info('神速支付回调错误提示：系统异常' . $exception->getMessage());
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
            case 'SHEN_SU_ALIPAY_QRCODE':
                $provider_key = 'S10103';
                break;
            case 'SHEN_SU_ALIPAY_HF':
                $provider_key = 'S10101';
                break;
            case 'SHEN_SU_ALIPAY_H5':
                $provider_key = 'S10105';
                break;
            case 'SHEN_SU_ALIPAY_ZZ':
                $provider_key = 'S10113';
                break;
            case 'SHEN_SU_ALIPAY_ZK':
                $provider_key = 'S10108';
                break;
            case 'SHEN_SU_ALIPAY_H5_SDK':
                $provider_key = 'S10107';
                break;
            case 'SHEN_SU_ALIPAY_HF_SDK':
                $provider_key = 'S10116';
                break;
            case 'SHEN_SU_WECHAT_HF':
                $provider_key = 'S10102';
                break;
            case 'SHEN_SU_WECHAT_H5':
                $provider_key = 'S10106';
                break;
            case 'SHEN_SU_YUNSHANFU':
                $provider_key = 'S10110';
                break;
            case 'SHEN_SU_UNIONPAY':
                $provider_key = 'S10111';
                break;
            default:
                $provider_key = 'S10101';
        }

        try {
            $key = $this->sdk_config['mch_key'] ?? '';
            $data = [
                'appId' => $this->sdk_config['mch_id'] ?? '',
                'outOrderNo' => $order->order_no,
                'amount' => $order->amount, //订单金额
                "attach" =>  date("Y-m-d H:i:s"),
                "timestamp" =>  time(),
                'detail' => 'shenma',
                'nonceStr' => (string)rand(10000000,99999999),
                'payWayCode' => $provider_key,
                'notifyUrl' => $this->sdk_config['callback'] ?? '',
                'outUserNo' => $order->user_id,
                'outUserIp' => getIp(),
            ];
            $data['sign'] = strtoupper(NormalSignature::encrypt($data,'sign','&key='.$key));
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $url = self::GATEWAY;
            $result = $this->post($url,$data); //发送请求
//            dd($data,$result);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('神速支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['code'] != '1'){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        } catch (\Exception $exception) {
            \Log::channel('sifang_pay_send')->info('神速请求异常:' . $exception->getMessage());
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
        //解析接口返回的数据
        $return_data = json_decode($order->return_data, true);
        header("Location:{$return_data['result']['payInfo']}");
//        return redirect($return_data['result']['payInfo']);
    }
    public function sdk(PaymentOrder $order)
    {
        $return_data = json_decode($order->return_data, true);
        return $return_data['result']['payInfo'];
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
        echo "SUCCESS";
        die;
    }
}
