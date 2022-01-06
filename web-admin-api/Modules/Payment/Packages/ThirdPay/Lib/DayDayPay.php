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
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class DayDayPay extends PayAbstract
{

    const NAME = '天天支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户APPID',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            'DayDay_WX' => self::NAME . '微信扫码',
        ],
        self::ALIPAY_QRCODE => [
            'DayDay_ZFB' => self::NAME . '支付宝扫码',
        ],
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
                \Log::channel('sifang_pay_callback')->info('天天支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = NormalSignature::encrypt($callback_data,'sign','&key='.$key); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('天天支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['total_fee'];
            $order = PaymentOrder::where('order_no', $callback_data['m_order_id'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money) {
                $order->third_order_no = $callback_data['sys_order_id'] ?? ''; //平台订单号
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('天天支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('天天支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('天天支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = '200';//成功

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
            case 'DayDay_ZFB':
                $provider_key  = 'alipay';
                break;
            case 'DayDay_WX':
                $provider_key  = 'wxpay';
                break;
            default:
                $provider_key = 'wxpay';
        }
        $key = $this->sdk_config['mch_key'] ?? '';
        //整理订单数据
        $data         = [
            'm_id'          => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'm_order_id'    => $order->order_no,
            'client_ip'     => getIp(),
            'total_fee'     => bcmul($order->amount,1,2),//订单金额
            'type'          => $provider_key,
            'notify_url'    => $this->sdk_config['callback'] ?? '',
            'format'        => 'json',
        ];
        $data['sign'] = NormalSignature::encrypt($data,'sign','&key='.$key); //生成签名
        $url          = $this->sdk_config['gateway']; //接口请求地址
        $start_time = date('Y-m-d H:i:s',time());//请求时间
        $result = $this->get($url, $data); //发送请求
        $end_time = date('Y-m-d H:i:s',time());//相应时间
        $res = json_decode($result['res'], true); //解析返回结果
        unset($res['data']['qrcode_img']);
        //dd($url,$data,$result,$res);
        \Log::channel('sifang_pay_send')->info('天天支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.json_encode($res,JSON_UNESCAPED_UNICODE));
        //判断请求是否成功
        if ($result['responseCode'] != 200 || $res['error_code'] != self::SUCCESS){
            return false;
        }
        //保存四方返回的数据
        $order->return_data = json_encode($res,JSON_UNESCAPED_UNICODE) ?? '';
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
        return redirect($return_data['data']['pay_url']);
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