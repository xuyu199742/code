<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 11:58
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;
use Modules\Payment\Packages\ThirdPay\Signature\RedBullSignature;

class YFTPay extends PayAbstract
{
    //商户私钥
    public $merchant_private_key = '-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAOwVeayRoOnH1045
kOD9cwGrTrQ4n7oQq8rWoKX2qhiL6TkflCP9aU/9U4Lchqvl6ERcDeh1bv0U643c
AwX/YBnZ3y7ufAGgZ23rw+K0/IcUTkc1aGm79mtHE4MkxsqK1hhNblsr6plQA7Dy
YqPTILbW+HCzCzaKyXIeWuPDM33LAgMBAAECgYA8/n9lGmrce4kg6LaJqnGgKMY4
wbhithPsX+85cbUYim1DGOmJMtuWkviUgq04lDmiD7Z4LH70XAdPq1wMnKITDLL8
Wa9xFIBUwlFTjWQOolYAcyH8BFznvhvP7Xx+7BGLVkJkyUIsJnypSIXJX3n1DVpm
KfRYuZysTTKy08fVAQJBAPZdsDdsTfQdkiIPusOzCmbjnPrf7CLJm5X4Uwv+s3Ut
01X39V53DzpmuugGOJPlHjVKxbSm8PFvxoUYPrEqqyECQQD1UNr+PwOA9yrKo8yF
OF8g26ecaLFFVvZaBphHhinx6IFTbBIMACCppig3+ZIN5L4yxYDV9HgwVzjn1diP
PhdrAkAVuW83g+pf01e2fzKV3SzWo82M5b/51VNN5ybTkPMcKx1OoF3XpaIHIVXr
7diBWhvO0Tgb1Pi8IYIc7GVi1ANhAkEApmQlZfYPxlXf7HqVqnbF71+NnIVWWBXY
GtAGUd0qbi6qKY9P3lvny6or9WuOKWZRq9ZSwMyFCSTgMD/YF7Ch6QJAMCDcVn6F
Wogd6adMfLqpKL1rPA6bssUaFNsH3y5n3UQ4OeqAGB2twJfU0Y8gWzWb6F6COoN2
EXquxgH3y19IRQ==
-----END PRIVATE KEY-----';

    //平台公钥
    public $debaozhifu_public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCcaBn+FZUqjk0BXKBbLmVNZDqC9ywYW+xNolo4ozs5W/xNr45joNwUTqd2U6CuFfaO80ybSSE5sOuYRFLTLX32cJljMciThv0jKCE8Bwe3c6KM6IIz9b3qkwwsHap3dwhdJq1Wj5wstsrAnUSU0TJ/p+azuw4hnlI2y4dpTXjhIQIDAQAB 
-----END PUBLIC KEY-----';


    const NAME = '易付通';
    const SUCCESS = 'SUCCESS'; //交易成功
    const FAILS = 3;//交易失败

    const CONFIG = [
        'gateway'       => '支付网关',
        'brandNo'       => '商户号',
    ];


    const APIS = [
        self::WECHAT_QRCODE => [
            'YFT_WECHAT_QRCODE'     => self::NAME . '微信扫码',
        ],
        self::ALIPAY_QRCODE => [
            'YFT_ALIPAY_QRCODE'     => self::NAME . '支付宝扫码',
        ]
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
                case 'YFT_WECHAT_QRCODE':
                    $provider_key  = 'wx_h5_scan';
                    break;
                case 'YFT_ALIPAY_QRCODE':
                    $provider_key  = 'ali_h5_scan';
                    break;
                default:
                    $provider_key  = 'ali_h5_scan';
            }
            //整理订单数据
            $data         = [
                'merchant_code'  => $this->sdk_config['brandNo'] ?? '', //商户编号
                'client_ip'      => getIp(),//商户端用户端IP
                'order_no'       => $order->order_no,//订单编号
                'order_amount'   => $order->amount,//订单金额
                'service_type'   => $provider_key,//通道，服务类型
                'notify_url'     => $this->sdk_config['callback'] ?? '',
                'interface_version' => 'V3.1',
                'order_time'    => date('Y-m-d H:i:s'),
                'product_name'  => 'pay',
            ];
            //生成签名
            $data['sign'] = $this->rsaSign(RedBullSignature::encrypt($data));
            $data['sign_type'] = "RSA-S";
            $url = $this->sdk_config['gateway'];
            $start_time = date('Y-m-d H:i:s',time());//相应时间
            $result = $this->req($url,$data); //发送请求
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode(json_encode($result['res']),true); //解析返回结果
//            dd($result['res']);
            \Log::channel('sifang_pay_send')->info('盈通支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.json_encode($result['res']));
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['response']['result_code'] != 0){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = json_encode($result['res']);
            return true; //不需要save，组件会自动save
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('易付通订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            $callback_data = request()->all();
            $original = $callback_data;
            //签名字串
            $signature  = $callback_data['sign'];
            unset($callback_data['sign']);
            unset($callback_data['sign_type']);
            //验签
            if(!$this->rsaCheck(RedBullSignature::encrypt($callback_data),$signature)){
                \Log::channel('sifang_pay_callback')->info('易付通回调错误提示：签名有误');
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['trade_status'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('易付通回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['order_no'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('易付通回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount != $callback_data['order_amount']){
                \Log::channel('sifang_pay_callback')->info('易付通回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->third_created_time  = date('Y-m-d H:i:s',strtotime($callback_data['trade_time']));//四方订单创建时间
            $order->third_order_no      = $callback_data['trade_no'];//四方平台订单号
            $order->callback_data       = json_encode($original, true);
            if ($order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('易付通支付回调成功提示：用户金币充值成功');
                return true;
            }else{
                \Log::channel('sifang_pay_callback')->info('易付通支付回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('易付通回调错误提示：系统异常'.$exception->getMessage());
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
        $return_data = json_decode($order->return_data,true); //解析返回结果
        if (isset($return_data['response']['payURL']) && $return_data['response']['payURL'] != '') {
            return redirect(urldecode($return_data['response']['payURL'])); //交易位置, 将使用者导向 (Redirect) 至此位置
        }
        return 'fail';
    }

    /**
     * 查询订单
     * @param PaymentOrder $order
     *
     * @return mixed
     */
    public function queryOrder(PaymentOrder $order)
    {
        return true;
    }

    public function success()
    {
        echo 'SUCCESS';
        die;
    }

    public function rsaSign($signStr){
        $merchant_private_key= openssl_get_privatekey($this->merchant_private_key);
        openssl_sign($signStr,$sign_info,$merchant_private_key,OPENSSL_ALGO_MD5);
        $sign = base64_encode($sign_info);
        return $sign;
    }

    public function rsaCheck($signStr,$dinpaySign){
        $dinpay_public_key = openssl_get_publickey($this->debaozhifu_public_key);
        if(!$dinpay_public_key){
            return false;
        }
        $flag = openssl_verify($signStr,base64_decode($dinpaySign),$this->debaozhifu_public_key,OPENSSL_ALGO_MD5);
        if($flag){
            return true;
        }else{
            return false;
        }
    }

    public function req($url,$postFields)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $res=simplexml_load_string($result);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array('responseCode' => $responseCode, 'res' => $res);
    }
}