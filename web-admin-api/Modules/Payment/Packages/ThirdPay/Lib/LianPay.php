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

class LianPay extends PayAbstract
{

    const NAME = '链支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户APPID',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            'LIAN_WX_QRCODE' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'LIAN_WX_H5' => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'LIAN_ALIPAY_QRCODE' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'LIAN_ALIPAY_H5'  => self::NAME . '支付宝H5',
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
                \Log::channel('sifang_pay_callback')->info('链支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            unset($callback_data['ext']);
            unset($callback_data['sign']);
            $key = $this->sdk_config['mch_key'] ?? '';
            ksort($callback_data);
            $selfSign = md5( urldecode( http_build_query($callback_data)).'&KEY='.$key); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('链支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['totalAmount'];
            $order = PaymentOrder::where('order_no', $callback_data['outOrderId'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money) {
                $order->third_order_no = $callback_data['instructCode'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s',strtotime($callback_data['transTime'] ?? time()));
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('链支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('链支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('链支付回调错误提示：系统异常'.$exception->getMessage());
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
        try{
            $order->order_no = 'limepay'.time().rand(10000,99999);
            //通道转换
            switch ($order->provider->provider_key){
                case 'LIAN_WX_QRCODE':
                    $provider_key  = '21';
                    break;
                case 'LIAN_WX_H5':
                    $provider_key  = '21';
                    break;
                case 'LIAN_ALIPAY_QRCODE':
                    $provider_key  = '30';
                    break;
                case 'LIAN_ALIPAY_H5':
                    $provider_key  = '30';
                    break;
                default:
                    $provider_key = '21';
            }
            if(strpos($order->provider->provider_key,'H5') !== false){
                $model = 'H5_PAY';
                $url = $this->sdk_config['gateway'].'/payment-pre-interface/order.do'; //接口请求地址
            }else{
                $model = 'QR_CODE';
                $url = $this->sdk_config['gateway'].'/payment-pre-interface/qrscan.do'; //接口请求地址
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $data         = [
                'merchantCode'   => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'amount'         => $order->amount * 100, //订单金额
                'orderCreateTime'=> date('YmdHis'),
                'outOrderId'     => $order->order_no,
                'payChannel'     => $provider_key,
                'noticeUrl'      => $this->sdk_config['callback'] ?? '',
            ];
            ksort($data);
            $data['sign'] = md5( urldecode( http_build_query($data)).'&KEY='.$key); //生成签名
            $data['ip'] = getIp();
            $data['model'] = $model;
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('链支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['code'] != self::SUCCESS){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('链支付请求异常:'.$exception->getMessage());
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
        return redirect($return_data['data']['url']);
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
        echo '00';
        die;
    }




}