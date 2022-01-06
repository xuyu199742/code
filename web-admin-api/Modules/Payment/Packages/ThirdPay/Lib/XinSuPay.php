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

class XinSuPay extends PayAbstract
{

    const NAME = '信速支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户密钥',
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            'wxsm' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'wxh5' => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'alism'     => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'alipay'     => self::NAME . '支付宝H5',
        ],
        self::YUNSHANFU => [
            'ylsm'     => self::NAME . '云闪付'
        ]
    ];

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            $data = request()->all();
            $sign = $data['sign'] ?? '';
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('信速支付回调错误提示：无返回');
                return false;
            }
            //整理订单数据
            $callback_data         = [
                'customerid'   => $data['customerid'],
                'status'        => $data['status'],
                'sdpayno'       => $data['sdpayno'],
                'sdorderno'     => $data['sdorderno'],
                'total_fee'     => $data['total_fee'],
                'paytype'       =>  $data['paytype'],
            ];
            $params = '';
            foreach ($callback_data as $key => $value) {
                $params .= $key . '=' . $value . '&';
            }
            $selfSign = md5($params.$this->sdk_config['mch_key']); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('信速支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            list($id,$user_id) = explode('o',$callback_data['sdorderno']);
            $order = PaymentOrder::where('id',$id)->where('user_id',$user_id)->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $callback_data['total_fee'] && $callback_data['status'] = self::SUCCESS) {
                $order->third_order_no = $callback_data['sdpayno'] ?? ''; //平台订单号
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('信速支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('信速支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('信速支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = 1;//成功

    /**
     * 请求四方订单方法
     *
     * @param PaymentOrder $order
     *
     * @return bool
     */
    public function send(PaymentOrder &$order): bool
    {
        //整理订单数据
        $data         = [
            'version'          => '1.0',
            'customerid'       => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'total_fee'        => bcmul($order->amount,1,2),//订单金额
            'sdorderno'        =>  $order->id.'o'.$order->user_id,  //因为这个四方限制订单号为20位以内
            'notifyurl'        => $this->sdk_config['callback'] ?? '',
            'returnurl'        => $this->sdk_config['callback'] ?? '',
        ];
        $params = '';
        foreach ($data as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        $data['sign'] = md5($params.$this->sdk_config['mch_key']); //生成签名
        $data['json'] = 0;
        $data['paytype'] = $order->provider->provider_key;
        $order->return_data = json_encode($data); //保存四方返回的数据
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
        try{
            $data = json_decode($order->return_data, true);
            $url          = $this->sdk_config['gateway']; //拼装请求地址
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $str = '信速支付请求时间：'.$start_time.'请求地址：'.$url.'请求数据：'.json_encode($data);
            if (json_decode($result['res'], true)){
                $str .= '请求返回数据：'.$result['res'];
            }
            \Log::channel('sifang_pay_send')->info($str);
            //判断请求是否成功
            if ($result['responseCode'] != 200){
                return '支付请求错误'.$result['responseCode'];
            }
            //如果返回json，并提示错误
            return $result['res'];
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('信速订单支付请求异常:'.$exception->getMessage());
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

}