<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/9
 * Time: 9:58
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use GuzzleHttp\Client;
use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;

class HengJiuPay extends PayAbstract
{

    const NAME = '恒玖支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::ALIPAY_H5 => [
            'HENGJIU_ALIPAY_H5'  => self::NAME . '支付宝H5',
        ],
        self::ALIPAY_QRCODE => [
            'HENGJIU_ALIPAY_QRCODE'  => self::NAME . '支付宝扫码',
        ],
        self::WECHAT_H5 => [
            'HENGJIU_WECHAT_H5'  => self::NAME . '微信H5',
        ],
        self::WECHAT_QRCODE => [
            'HENGJIU_WECHAT_QRCODE'  => self::NAME . '微信扫码',
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
            $sign = $callback_data['sign'];
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('恒玖支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $signData = [
                'status' => $callback_data['status'],
                'amount' => $callback_data['amount'],
                'order_no' => $callback_data['order_no'],
                'order_status' => $callback_data['order_status'],
                'pay_time' => $callback_data['pay_time'],
                'app_secret' => $key,
            ];
            $selfSign = strtolower(md5(http_build_query($signData))); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('恒玖支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['amount'] / 100;
            $status = $callback_data['order_status'];
            $order = PaymentOrder::where('order_no',$callback_data['order_no'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('恒玖支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('恒玖支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money && $status == self::SUCCESS) {
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('恒玖支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('恒玖支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('恒玖支付回调错误提示：用户金币充值失败，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('恒玖支付回调错误提示：系统异常'.$exception->getMessage());
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
            //通道转换
            switch ($order->provider->provider_key){
                case 'HENGJIU_ALIPAY_H5':
                    $provider_key  = 'alipay_wp';
                    break;
                case 'HENGJIU_ALIPAY_QRCODE':
                    $provider_key  = 'alipay_pc';
                    break;
                case 'HENGJIU_WECHAT_H5':
                    $provider_key  = 'wechat_wp';
                    break;
                case 'HENGJIU_WECHAT_QRCODE':
                    $provider_key  = 'wechat_pc';
                    break;
                default:
                    $provider_key  = 'alipay_pc';
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $notify_url    = $this->sdk_config['callback'] ?? '';
            $return_url    = $this->sdk_config['callback'] ?? '';
            //整理订单数据
            $data         = [
                'app_id'        => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'amount'        => $order->amount * 100, //订单金额
                'order_no'      => $order->order_no,
                'device'        => $provider_key,
                'app_secret'    => $key,
            ];
            //生成签名
            $sign = md5(http_build_query($data) . "&notify_url={$notify_url}");
            //构造请求链接
            $data['notify_url'] = $notify_url;
            $data['return_url'] = $return_url;
            $data['sign']       = $sign;
            unset($data['app_secret']);
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('恒玖支付请求异常:'.$exception->getMessage());
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
        $url = $this->sdk_config['gateway'];  //接口请求地址
        $return_data = json_decode($order->return_data, true);
        $client = new Client();
        $response = $client->request('GET', $url.'?'.http_build_query($return_data));
        return $response->getBody()->getContents();
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
