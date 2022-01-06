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

class YiLianPay extends PayAbstract
{

    const NAME = '易连支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'YILIAN_WX_H5' => self::NAME . '微信H5',
        ],
        self::WECHAT_QRCODE => [
            'YILIAN_WX_QRCODE' => self::NAME . '微信扫码',
        ],
        self::ALIPAY_H5 => [
            'YILIAN_ALIPAY_H5'  => self::NAME . '支付宝H5',
        ],
        self::ALIPAY_QRCODE => [
            'YILIAN_ALIPAY_QRCODE'  => self::NAME . '支付宝扫码',
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
                \Log::channel('sifang_pay_callback')->info('易连支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = strtoupper(NormalSignature::encrypt($callback_data,'sign','&appkey='.$key)); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('易连支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['amount'] / 100;
            $status = $callback_data['orderStatusCode'];
            list($id,$user_id) = explode('o',$callback_data['merchantOrderNo']);
            $order = PaymentOrder::where('id',$id)->where('user_id',$user_id)->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('易连支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('易连支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['platformOrderNo'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('易连支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('易连支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('易连支付回调错误提示：用户金币充值失败，回调状态为'.$status.'，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('易连支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = 'SUCCESS';//成功

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
                case 'YILIAN_WX_H5':
                    $provider_key  = 'WEIXIN_H5';
                    break;
                case 'YILIAN_WX_QRCODE':
                    $provider_key  = 'WEIXIN_QRCODE';
                    break;
                case 'YILIAN_ALIPAY_H5':
                    $provider_key  = 'ALIPAY_H5';
                    break;
                case 'YILIAN_ALIPAY_QRCODE':
                    $provider_key  = 'ALIPAY_QRCODE';
                    break;
                default:
                    $provider_key  = 'ALIPAY_H5';
            }
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $data         = [
                'paymentType'      => $provider_key,
                'merchantNo'       => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'orderTime'    => (string)date('YmdHis'),
                'goodsName'      => 'laowang_pay',
                'amount'       => (string)($order->amount * 100), //订单金额
                'clientIp'    => getIp(),
                'notifyUrl'   => $this->sdk_config['callback'] ?? '',
                'buyerId'       => (string)$order->user_id,
                'buyerName'     => (string)$order->user_id,
                'mchOrderNo'     => $order->id.'o'.$order->user_id,
                'nonceStr'     => (string)rand(10000000,99999999),
            ];
            $data['sign'] = strtoupper(NormalSignature::encrypt($data,'sign','&appkey='.$key)); //生成签名
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url,$data); //发送请求
//            dd($data,$result);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('易连支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['returnCode'] != self::SUCCESS){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('易连支付请求异常:'.$exception->getMessage());
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
        echo 'success';
        die;
    }

    public function post($url, $data = array())
    {
        $response    = $this->makeHttpRequest($url, self::METHOD_POST, $data);
        return $response;
    }

    public function makeHttpRequest($url, $method,  $postFields = NULL)
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
                "Content-Type:application/json"
            ),
        ));
        $result       = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array('responseCode' => $responseCode, 'res' => $result);
    }

}