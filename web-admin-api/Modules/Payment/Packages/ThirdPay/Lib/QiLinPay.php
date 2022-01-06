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

class QiLinPay extends PayAbstract
{

    const NAME = '麒麟付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户Token',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'QILIN_WX' => self::NAME . '微信支付',
        ],
        self::ALIPAY_H5 => [
            'QILIN_ZFB' => self::NAME . '支付宝支付',
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
            $sign = $callback_data['key'] ?? '';
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('麒麟支付回调错误提示：无返回');
                return false;
            }
            $callback_data['token'] = $this->sdk_config['mch_key'];
            $selfsign = strtolower(NormalSignature::encrypt($callback_data, 'key'));
            if ($selfsign != $sign) {
                \Log::channel('sifang_pay_callback')->info('麒麟支付回调错误提示：签名有误,内部签名：' . $selfsign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['price'];
            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money) {
                $order->third_order_no = $callback_data['paysapi_id'] ?? ''; //平台订单号
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('麒麟支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('麒麟支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('麒麟支付回调错误提示：系统异常'.$exception->getMessage());
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
        //整理订单数据
        $data         = [
            'price'        => $order->amount,//订单金额
            'version'      => 2,
            'istype'       => $order->provider->provider_key == 'QILIN_WX' ? 2 : 1,
            'uid'          => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'notify_url'  => $this->sdk_config['callback'] ?? '',
            'return_url'  => $this->sdk_config['callback'] ?? '',
            'orderuid'    => $order->user_id,
            'orderid'     => $order->order_no,
            'goodsname'   => '用户充值',
            'token'       => $this->sdk_config['mch_key']
        ];
        $data['key'] = strtolower(NormalSignature::encrypt($data, 'key')); //生成签名
        unset($data['token']);
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
            $str = '麒麟支付请求时间：'.$start_time.'请求地址：'.$url.'请求数据：'.json_encode($data);
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
            \Log::channel('sifang_pay_send')->info('麒麟订单支付请求异常:'.$exception->getMessage());
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