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

class JiaBaoPay extends PayAbstract
{

    const NAME = '加宝';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::ALIPAY_H5 => [
            'JIABAO_ALIPAY_H5'  => self::NAME . '支付宝H5',
        ],
        self::ALIPAY_QRCODE => [
            'JIABAO_ALIPAY_QRCODE'  => self::NAME . '支付宝扫码',
        ],
        self::WECHAT_H5 => [
            'JIABAO_WECHAT_H5'  => self::NAME . '微信H5',
        ],
        self::WECHAT_QRCODE => [
            'JIABAO_WECHAT_QRCODE'  => self::NAME . '微信扫码',
        ],
    ];

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            $data = request()->getContent();
            $callback_data = json_decode($data,true);
            $original = json_decode($callback_data['content'], true);
            $sign = $original['sign'];
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('加宝回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = $this->makeSign($original,$key); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('加宝回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $original['amount'];
            $status = $original['tradeStatus'];
            $order = PaymentOrder::where('order_no',$original['merchantTradeNo'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('加宝回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('加宝回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $original['tradeNo'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('加宝回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('加宝回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('加宝回调错误提示：用户金币充值失败，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('加宝回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = 'PAYMENT_SUCCESS'; //成功

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
                case 'JIABAO_ALIPAY_H5':
                    $provider_key  = 'AlipayBank';
                    break;
                case 'JIABAO_ALIPAY_QRCODE':
                    $provider_key  = 'Alipay';
                    break;
                case 'JIABAO_WECHAT_H5':
                    $provider_key  = 'WechatBank';
                    break;
                case 'JIABAO_WECHAT_QRCODE':
                    $provider_key  = 'Wechat';
                    break;
                default:
                    $provider_key  = 'Alipay';
            }
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $param         = [
                'merchantCode'      => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'channel'           => $provider_key,
                'amount'            => number_format(floatval($order->amount), 2, '.', ''),//订单金额
                'merchantTradeNo'   => $order->order_no,
                'terminalType'      => isset($_SERVER['HTTP_X_WAP_PROFILE']) ? 2 : 1,
                'notifyUrl'         => $this->sdk_config['callback'] ?? '',
                'returnUrl'         => $this->sdk_config['callback'] ?? '',
                'userId'            => $order->user_id,
            ];
            //生成签名
//            $param['sign'] = NormalSignature::encrypt($param,'',$key); //生成签名
            $param['sign'] = $this->makeSign($param,$key); //生成签名
            $data = [
                'merchantCode' => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'signType'     => 'md5',
                'content'      => json_encode($param)
            ];
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->POST($url,$data); //发送请求
//            dd($data,$result);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('加宝支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            $success = $res['status'] ?? '';
            if ($result['responseCode'] != 200 || $success != 'SUCCESS'){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('加宝请求异常:'.$exception->getMessage());
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
        $data = json_decode($return_data['data'],true);
        $content = json_decode($data['content'], true);
        return redirect($content['payUrl']);
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

    public function POST($url, $postFields = null)
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
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($postFields),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json;charset=UTF-8"
            ),
        ));
        $result       = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array('responseCode' => $responseCode, 'res' => $result);
    }

    function makeSign($data, $key)
    {
        ksort($data);
        reset($data);
        $md5str = "";
        foreach ($data as $k => $val) {
            if (in_array($k, ['sign']) || $val == '' || !$val) {
                continue;
            }
            $md5str .= $val;
        }
        return md5($md5str . $key);
    }

}
