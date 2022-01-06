<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 14:06
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class KuaiFuPay extends PayAbstract
{
    const NAME = '快富';//四方名称
    const SUCCESS = '0';//四方支付成功异步返回标识
    //商户信息配置
    const CONFIG = [
        'gateway'      => '支付网关',
        'pay_memberid' => '商户号',
        'pay_key'      => '密钥',
    ];
    //支付通道配置
    const APIS = [
        self::ALIPAY_QRCODE => [
            'KF_ZFB_QRCODE' => self::NAME . '支付宝扫码',
        ],
    ];

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

    //表单提交，即收银台模式
    public function send(PaymentOrder &$order): bool
    {
        try{
            //通道转换
            switch ($order->provider->provider_key){
                case 'KF_ZFB_QRCODE':
                    $provider_key  = 3;
                    break;
                default:
                    $provider_key = 3;
            }
            //需要加入签名的数据
            $data = [
                'appid'         => $this->sdk_config['pay_memberid'] ?? '',//商户唯一标识，注册后获得
                'good'          => '用户充值',//商品名称
                'paytype'       => $provider_key,//3支付宝任意码，其他方式暂不开放
                'orderno'       => $order->order_no,//自定义订单号，不可重复
                'timestamp'     => time(),//时间戳秒
                'amount'        => intval($order->amount * 100),//单位：分
                'returnurl'     => $this->sdk_config['callback'] ?? '',//支付跳转
                'notifyurl'     => $this->sdk_config['callback'] ?? '',//支付回调
                'ip'            => getIp(),//用于分配支付通道
            ];
            //签名
            $data['sign'] = NormalSignature::signature($data,'&key='.$this->sdk_config['pay_key']);

            $url          = $this->sdk_config['gateway']; //接口请求地址
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            //dd($url,$data,$result,$res);
            \Log::channel('sifang_pay_send')->info('快富支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['code'] != 0){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('快富订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('快富订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //获取需要加入签名的数据
            $callback_data['appid']          = request('appid');//商户唯一标识，注册后获得
            $callback_data['amount']         = request('amount');//单位：分
            $callback_data['orderno']        = request('orderno');//自定义订单号，不可重复
            //对应的签名
            $sign                            = request('sign');
            //签名
            $selfsign  = NormalSignature::signature($callback_data,'&key='.$this->sdk_config['pay_key']);
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('快富回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //验证订单是否支付成功---接口暂无
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['orderno'])->first();
            if (!$order){
                if ($order->payment_status == PaymentOrder::SUCCESS){
                    return true;
                }
                \Log::channel('sifang_pay_callback')->info('快富回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if (intval($order->amount * 100) != $callback_data['amount']){
                \Log::channel('sifang_pay_callback')->info('快富回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['orderno'];
            $order->success_time   = date('Y-m-d H:i:s',time());
            $order->callback_data  = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('快富回调错误提示：用户金币充值失败');
                return false;
            }
            //{"appid":"4221693571665","amount":"20000","orderno":"d25fed39d599420d9ae452b4f62ff317","sign":"23306EA5ED5FD0362EE8CD86C2C1B5D2"}
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('快富回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        //解析接口返回的数据
        $return_data = json_decode($order->return_data, true);
        //跳转到指定链接
        return redirect($return_data['jumpUrl']);
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
    public function success()
    {
        echo 'SUCCES';
        die;
    }
}