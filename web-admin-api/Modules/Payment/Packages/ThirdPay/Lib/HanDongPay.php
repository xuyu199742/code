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

class HanDongPay extends PayAbstract
{

    const NAME = '汉东支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户APPID',
        'mch_key' => '商户APPKEY',
    ];

    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'HD_WXSCAN' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'HD_WXH5'     => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'HD_ALISCAN' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'HD_ALIH5'     => self::NAME . '支付宝H5',
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
                \Log::channel('sifang_pay_callback')->info('汉东支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            unset($callback_data['attach']);
            unset($callback_data['sign']);
            $selfSign = $this->encrypt($callback_data,$this->sdk_config['mch_key']);
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('汉东支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['amount'];
            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money && $callback_data['returncode'] == self::SUCCESS) {
                $order->third_order_no = $callback_data['transaction_id'] ?? ''; //平台订单号
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('汉东支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('汉东支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('汉东支付回调错误提示：系统异常'.$exception->getMessage());
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
            case 'HD_WXH5':
                $provider_key  = '901';
                break;
            case 'HD_WXSCAN':
                $provider_key  = '902';
                break;
            case 'HD_ALISCAN':
                $provider_key  = '903';
                break;
            case 'HD_ALIH5':
                $provider_key  = '904';
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
        $data['pay_productname'] = 'pay';
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
        //解析接口返回的数据
        $data = json_decode($order->return_data, true);
        $url          = $this->sdk_config['gateway']; //拼装请求地址
        $start_time = date('Y-m-d H:i:s',time());//请求时间
        $result = $this->post($url, $data); //发送请求
        $str = '汉东支付请求时间：'.$start_time.'请求地址：'.$url.'请求数据：'.json_encode($data);
        if ($result['res'] ?? ''){
            $str .= '请求返回数据：'.$result['res'];
        }
        \Log::channel('sifang_pay_send')->info($str);
        //判断请求是否成功
        if ($result['responseCode'] != 200){
            return '支付请求错误'.$result['responseCode'];
        }
        //如果返回json，并提示错误
        return $result['res'];
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