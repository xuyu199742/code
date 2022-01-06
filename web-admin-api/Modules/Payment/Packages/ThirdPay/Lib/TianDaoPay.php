<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/9
 * Time: 9:58
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class TianDaoPay extends PayAbstract
{

    const NAME = '天道支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户APPID',
        'mch_key' => '商户APPKEY',
    ];

    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'TD_WXSCAN' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'TD_WXH5'     => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'TD_ALISCAN' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'TD_ALIH5'     => self::NAME . '支付宝H5',
        ],
        self::YUNSHANFU => [
            'TD_YUN'     => self::NAME . '云闪付'
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
                \Log::channel('sifang_pay_callback')->info('天道支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            unset($callback_data['attach']);
            unset($callback_data['sign']);
            $selfSign = $this->encrypt($callback_data,$this->sdk_config['mch_key']);
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('天道支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['amount'];
            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money && $callback_data['returncode'] == self::SUCCESS) {
                $order->third_order_no = $callback_data['transaction_id'] ?? ''; //平台订单号
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('天道支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('天道支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('天道支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = '00';//成功

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
            case 'TD_WXSCAN':
                $provider_key  = '902';
                break;
            case 'TD_WXH5':
                $provider_key  = '914';
                break;
            case 'TD_ALISCAN':
                $provider_key  = '903';
                break;
            case 'TD_ALIH5':
                $provider_key  = '904';
                break;
            case 'TD_YUN':
                $provider_key  = '916';
                break;
            default:
                $provider_key = '902';
        }
        //整理订单数据
        $data         = [
            'pay_amount'        => number_format($order->amount,2, '.', ''),//订单金额
            'pay_bankcode'       => $provider_key,
            'pay_memberid'          => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'pay_notifyurl'  => $this->sdk_config['callback'] ?? '',
            'pay_callbackurl'  => $this->sdk_config['callback'] ?? '',
            'pay_orderid'     => $order->order_no,
            'pay_applydate'   => date('Y-m-d H:i:s'),
        ];
        $data['pay_md5sign'] = $this->encrypt($data, $this->sdk_config['mch_key']); //生成签名
        $data['clientip']  = getIp();
        $data['pay_returnType'] = 'json';
        $url = $this->sdk_config['gateway']; //接口请求地址
        $start_time = date('Y-m-d H:i:s',time());//请求时间
        $result = $this->post($url, $data); //发送请求
        $end_time = date('Y-m-d H:i:s',time());//相应时间
        $res = json_decode($result['res'], true); //解析返回结果
        //dd($url,$data,$result,$res);
        \Log::channel('sifang_pay_send')->info('天道支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
        //判断请求是否成功
        if ($result['responseCode'] != 200 || $res['status'] != '0000'){
            return false;
        }
        //保存四方返回的数据
        $order->return_data = $result['res'] ?? '';
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
        //解析接口返回的数据
        $return_data = json_decode($order->return_data, true);
        return redirect($return_data['pay_info']);
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

    public function encrypt(array $data,$keyValue)
    {
        ksort($data);
        return strtoupper(md5(urldecode(http_build_query($data)) . "&key=$keyValue"));
    }

    public function success()
    {
        echo 'OK';
        die;
    }

}