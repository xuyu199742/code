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

class HXPay extends PayAbstract
{

    const NAME = '火星支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户密钥',
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            'WECHAT_NATIVE' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'WECHAT_H5' => self::NAME . '微信H5',
        ],
        self::ALIPAY_H5 => [
            'ALI_H5'     => self::NAME . '支付宝H5',
        ],
    ];

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            $callback_data = json_decode(file_get_contents('php://input'), true);
            if(!$callback_data){
                \Log::channel('sifang_pay_callback')->info('火星支付回调错误提示：无返回');
                return false;
            }
            $callback_data['key'] = $this->sdk_config['mch_key'];
            $selfSign = strtolower(NormalSignature::encrypt($callback_data, 'sign'));
            if ($selfSign != $callback_data['sign']) {
                \Log::channel('sifang_pay_callback')->info('火星支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['money'] ?? 0;
            $order = PaymentOrder::where('order_no', $callback_data['merchantOrderNumber'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money/100 && $callback_data['orderStatus'] == self::SUCCESS) {
                $order->third_order_no = $callback_data['orderNumber'] ?? ''; //平台订单号
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('火星支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('火星支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('火星支付回调错误提示：条件未通过');
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('火星支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = 'SUC';//成功

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
            'amount'           => $order->amount*100,//订单金额
            'currency'         =>  "CNY",
            'payType'          => $order->provider->provider_key,
            'merchantNumber'  => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'notifyUrl'        => $this->sdk_config['callback'] ?? '',
            'orderNumber'      => $order->order_no,
            'commodityName'    => urlencode(mb_convert_encoding('userPay', 'UTF-8')),
            'commodityDesc'    => urlencode(mb_convert_encoding('userPay', 'UTF-8')),
            'orderCreateIp'    => getIp(),
            'key'               => $this->sdk_config['mch_key']
        ];
        $data['sign'] = strtolower(NormalSignature::encrypt($data)); //生成签名
        unset($data['key']);
        $url          = $this->sdk_config['gateway']; //接口请求地址
        $start_time = date('Y-m-d H:i:s',time());//请求时间
        $result = $this->http_post_data($url, json_encode($data)); //发送请求
        $end_time = date('Y-m-d H:i:s',time());//相应时间
        $res = json_decode($result['res'], true); //解析返回结果
//        dd($url,$data,$result,$res);
        \Log::channel('sifang_pay_send')->info('火星支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
        //判断请求是否成功
        if ($result['responseCode'] != 200 || $res['message']['code'] != 200){
            return false;
        }
        $order->return_data = $result['res'] ?? ''; //保存四方返回的数据
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
        return redirect($return_data['context']['payurl']);
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

    public function http_post_data($url, $data_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json; charset=utf-8",
                "Content-Length: " . strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        $res['res'] = ob_get_contents();
        ob_end_clean();
        $res['responseCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $res;
    }

    public function success()
    {
        echo 'SUC';die;
    }

    public function fail()
    {
        echo 'FAIL';die;
    }
}