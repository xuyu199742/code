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

class JinLiuPay extends PayAbstract
{

    const NAME = '金流支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::ALIPAY_QRCODE => [
            'JINLIU_ALIPAY_QRCODE'  => self::NAME . '支付宝扫码',
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
                \Log::channel('sifang_pay_callback')->info('金流支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $signData = [
                'mchId' => $callback_data['mchId'],
                'orderId' => $callback_data['orderId'],
                'orderNo' => $callback_data['orderNo'],
                'oriPrice' => $callback_data['oriPrice'],
                'payDateTime' => $callback_data['payDateTime'],
                'times' => $callback_data['times'],
            ];
            $selfSign = strtoupper(md5(urldecode(http_build_query($signData).$key))); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('金流支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['oriPrice'];
            $order = PaymentOrder::where('order_no',$callback_data['orderNo'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('金流支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('金流支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $callback_data['orderId'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('金流支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('金流支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('金流支付回调错误提示：用户金币充值失败，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('金流支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = '0';//成功

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
                case 'JINLIU_ALIPAY_QRCODE':
                    $provider_key  = '003';
                    break;
                default:
                    $provider_key  = '003';
            }
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $data         = [
                'channelCode'   => $provider_key,
                'ip'            => getIp(),
                'mchId'         => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'notifyUrl'     => $this->sdk_config['callback'] ?? '',
                'orderNo'       => $order->order_no,
                'oriPrice'      => number_format(floatval($order->amount), 2, '.', ''), //订单金额
                'platform'      => '',
                'returnUrl'     => '',
                'times'         => str_replace('.','',microtime(true)),
            ];
            $data['sign'] = strtoupper(md5(urldecode(http_build_query($data).$key))); //生成签名
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url,$data); //发送请求
//            dd($data,$result);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('金流支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            $success = $res['code'] ?? '';
            if ($result['responseCode'] != 200 || $success != self::SUCCESS){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('金流支付请求异常:'.$exception->getMessage());
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
        return redirect($return_data['data']['payUrl']);
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