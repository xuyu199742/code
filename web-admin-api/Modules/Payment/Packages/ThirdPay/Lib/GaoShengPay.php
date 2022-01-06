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

class GaoShengPay extends PayAbstract
{

    const NAME = '高盛支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => 'PDD-商户号',
        'mch_key' => 'PDD-商户秘钥',
        'mch_id_t'  => '淘宝红包-商户号',
        'mch_key_t' => '淘宝红包-商户秘钥',
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            'GAOSHENG_WX_QRCODE' => self::NAME . '微信',
        ],
        self::ALIPAY_QRCODE => [
            'GAOSHENG_ALIPAY_QRCODE' => self::NAME . '支付宝PDD',
        ],
        self::ALIPAY_H5 => [
            'GAOSHENG_ALIPAY_H5' => self::NAME . '淘宝红包',
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
                \Log::channel('sifang_pay_callback')->info('高盛支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $code = $callback_data['code'];  //状态信息1000代表成功
            $customerNo = $callback_data['customerNo']; //客户编号
            $customerOrderNo = $callback_data['customerOrderNo']; //客户订单编号
            $orderNo = $callback_data['orderNo']; //平台订单号
            $amount = $callback_data['amount'];//订单提交金额
            $tradingTime = $callback_data['tradingTime'];//交易时间
            $order = PaymentOrder::where('order_no',$customerOrderNo)->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('高盛支付回调错误提示：该订单未找到');
                return false;
            }
            $mch_key = $this->sdk_config['mch_key'] ?? '';
            $mch_key_t = $this->sdk_config['mch_key_t'] ?? '';
            $secretKey = $order->provider->provider_key == 'GAOSHENG_ALIPAY_H5' ? $mch_key_t : $mch_key;
            $selfSign = md5("{$secretKey}|{$code}|{$customerNo}|{$orderNo}|{$customerOrderNo}|{$amount}|{$tradingTime}");  //验证签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('高盛支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            if ($order->amount == $amount && $code == self::SUCCESS) {
                $order->third_order_no = $orderNo; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s',strtotime($tradingTime));
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('高盛支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('高盛支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('高盛支付回调错误提示：用户金币充值失败，回调状态为'.$code.'，金额为'.$amount);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('高盛支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = '1000';//成功

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
                case 'GAOSHENG_WX_QRCODE':
                    $provider_key  = '2';
                    break;
                case 'GAOSHENG_ALIPAY_QRCODE':
                    $provider_key  = '1';
                    break;
                case 'GAOSHENG_ALIPAY_H5':
                    $provider_key  = '6';
                    break;
                default:
                    $provider_key  = '1';
            }
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $mch_key = $this->sdk_config['mch_key'] ?? '';
            $mch_id = $this->sdk_config['mch_id'] ?? '';
            $mch_key_t = $this->sdk_config['mch_key_t'] ?? '';
            $mch_id_t = $this->sdk_config['mch_id_t'] ?? '';
            $key = $provider_key == '6' ? $mch_key_t : $mch_key;
            $customerNo = $provider_key == '6' ? $mch_id_t : $mch_id;
            //整理订单数据
            $data         = [
                'customerNo'     => $customerNo, //设置配置参数
                'productType'    => $provider_key,
                'tradeType'      => 1,
                'amount'         => $order->amount, //订单金额
                'customerOrderNo'=> $order->order_no,
                'notifyUrl'      => $this->sdk_config['callback'] ?? '',
            ];
            $data['sign'] = \md5($key . '|' . $data['customerNo'] . '|' . $data['customerOrderNo'] . '|' . $data['amount'] . '|' . $data['notifyUrl']); //加密算法
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url,$data); //发送请求
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('高盛支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['code'] != self::SUCCESS){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('高盛支付请求异常:'.$exception->getMessage());
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
        return redirect($return_data['payUrl']);
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