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

class JiaYingPay extends PayAbstract
{

    const NAME = '嘉盈支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'JIAYING_WX_H5' => self::NAME . '微信H5',
        ],
        self::ALIPAY_H5 => [
            'JIAYING_ALIPAY_H5'  => self::NAME . '支付宝H5',
        ],
        self::UNIONPAY_QRCODE => [
            'JIAYING_UNIONPAY_QRCODE'  => self::NAME . '银联',
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
            $sign = $callback_data['sign'];
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('嘉盈支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = NormalSignature::encrypt($callback_data,'sign',$key); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('嘉盈支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['amount'];
            $status = $callback_data['status'];
            $order = PaymentOrder::where('order_no',$callback_data['custom_no'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('嘉盈支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('嘉盈支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['order_no'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s',strtotime($callback_data['put_at']));
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('嘉盈支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('嘉盈支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('嘉盈支付回调错误提示：用户金币充值失败，回调状态为'.$status.'，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('嘉盈支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = '4';//成功

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
                case 'JIAYING_WX_H5':
                    $provider_key  = 'WeChat';
                    break;
                case 'JIAYING_ALIPAY_H5':
                    $provider_key  = 'AliPay';
                    break;
                case 'JIAYING_UNIONPAY_QRCODE':
                    $provider_key  = 'UnionPay';
                    break;
                default:
                    $provider_key  = 'AliPay';
            }
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $data         = [
                'custom_no'     => $order->order_no,
                'pay_type'      => $provider_key,
                'merchant'       => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'amount'        => number_format(floatval($order->amount), 2, '.', ''), //订单金额
                'notify_url'    => $this->sdk_config['callback'] ?? '',
                'return_url'    => $this->sdk_config['callback'] ?? '',
            ];
            $data['sign'] = NormalSignature::encrypt($data,'sign',$key); //生成签名
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url,$data); //发送请求
//            dd($data,$result);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('嘉盈支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            $success = $res['success'] ?? '';
            if ($result['responseCode'] != 200 || $success != true){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('嘉盈支付请求异常:'.$exception->getMessage());
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
        return redirect($return_data['data']['responses']['JumpUrl']);
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