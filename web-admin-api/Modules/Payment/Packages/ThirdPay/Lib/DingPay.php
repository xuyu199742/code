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

class DingPay extends PayAbstract
{

    const NAME = '叮叮支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'DING_WX_H5' => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'DING_ALIPAY_QRCODE' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'DING_ALIPAY_H5'  => self::NAME . '支付宝H5',
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
            $original = [
                'account' => $callback_data['account'],
                'clientOrderId' =>  $callback_data['clientOrderId'],
                'money' =>  $callback_data['money'],
                'orderId' =>  $callback_data['orderId'],
                'payStatus' =>  $callback_data['payStatus'],
            ];
            $sign = $callback_data['secretKey'] ?? '';
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('叮叮支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = $this->encrypt($original,'secretKey','&publicKey='.$key); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('叮叮支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['money'];
            $status = $callback_data['payStatus'];
            $order = PaymentOrder::where('order_no', $callback_data['clientOrderId'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('叮叮支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('叮叮支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money / 100 && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['orderId'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('叮叮支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('叮叮支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('叮叮支付回调错误提示：用户金币充值失败，回调状态为'.$status.'，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('叮叮支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = '1';//成功

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
                case 'DING_WX_H5':
                    $provider_key  = '2';
                    break;
                case 'DING_ALIPAY_QRCODE':
                    $provider_key  = '3';
                    break;
                case 'DING_ALIPAY_H5':
                    $provider_key  = '1';
                    break;
                default:
                    $provider_key  = '2';
            }
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $data         = [
                'account'      => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'callback'     => $this->sdk_config['callback'] ?? '',
                'clientOrderId'=> $order->order_no,
                'clientUserId' => $order->user_id,
                'clientUserIp' => getIp(),
                'money'        => $order->amount * 100, //订单金额
                'payType'      => $provider_key,
                'subject'      => 'ding_pay',
            ];
            $data['secretKey'] = $this->encrypt($data,'secretKey','&publicKey='.$key); //生成签名
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url,$data); //发送请求
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('叮叮支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['code'] != '0'){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('叮叮支付请求异常:'.$exception->getMessage());
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

    public function encrypt(array $data, string $without = 'sign',string $append = '')
    {
        $params = '';
        foreach ($data as $key => $value) {
            if ($value === '' || $value == null || $key == $without) {
                continue;
            }
            $params .= $key . '=' . $value . '&';
        }
        $params = rtrim($params, '&');
        return strtoupper(md5($params . $append));
    }

    public function post($url, $data = array())
    {
        $response    = $this->makeHttpRequest($url, self::METHOD_POST, $data);
        return $response;
    }

    public function makeHttpRequest($url, $method, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS =>json_encode($postFields),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        ));
        $result       = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array('responseCode' => $responseCode, 'res' => $result);
    }

}