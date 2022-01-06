<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/9
 * Time: 9:58
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\CYTSignature;

class CYTPay extends PayAbstract
{

    const NAME = '诚易通支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            'UFWECHAT' => self::NAME . '微信扫码支付',
        ],
        self::ALIPAY_QRCODE => [
            'UFALIPAY' => self::NAME . '支付宝扫码支付',
        ],
    ];

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            $data = request()->all();
            $callback_data = json_decode($data, true);
            //待解密字符串
            $reqdata = $callback_data["reqdata"];
            //判断字符串是否需要 urldecode解码
            if(strpos($reqdata,"%")){
                $reqdata =  urldecode($reqdata);
            }
            $cty = new CYTSignature();
            $dataJson = $cty->privateDecrypt($reqdata);
            if(!$dataJson){
                \Log::channel('sifang_pay_callback')->info('诚易通支付回调错误提示：解密失败|'.$callback_data);
                return false;
            }
            $d_data = json_decode($dataJson,true);
            $c_data = $d_data;
            if(!$c_data['orderstatus'] != 1){
                \Log::channel('sifang_pay_callback')->info('诚易通支付回调错误提示：返回'.$c_data);
                return false;
            }
            //去除不需要参入验签的字段
            unset($d_data["sysnumber"]);
            unset($d_data["attach"]);
            $verify = $cty->payVerify($d_data);
            if (!$verify) {
                \Log::channel('sifang_pay_callback')->info('诚易通支付回调错误提示：签名验证失败，返回数据：' . json_encode($c_data));
                return false;
            }
            $order = PaymentOrder::where('order_no', $c_data['ordernumber'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $c_data['paymoney']) {
                $order->third_order_no = $c_data['sysnumber'] ?? ''; //平台订单号
                $order->callback_data = json_encode($c_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('诚易通支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('诚易通支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('诚易通支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = 1;//成功

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
            'p1_mchtid'  => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'p2_paytype'  => $order->provider->provider_key,
            'p3_paymoney'  => $order->amount,
            'p4_orderno'  => $order->order_no,
            'p5_callbackurl'  => $this->sdk_config['callback'] ?? '',
            'p6_notifyurl'  => $this->sdk_config['callback'] ?? '',
            'p7_version'  => 'v2.9',
            'p8_signtype'  => 2,
            'p9_attach'    => 'chongzhi',
            'p10_appname'  => 'cyt',
            'p11_isshow'     => 0,
            'p12_orderip'   => getIp(),
            'p13_memberid'=> $order->user_id,
        ];
        $cty = new CYTSignature();
        $str = $cty->get_sign($data);
        //生成签名
        $data["sign"] = $cty->sign($str);
        //转为json字符串
        $dataJson = json_encode($data);
        //RSA公钥加密
        $reqdata =urlencode($cty->publicEncrypt($dataJson));
        //请求参数
        $data["mchtid"] = $data["p1_mchtid"];
        $data["reqdata"] = $reqdata;
        $url = $this->sdk_config['gateway']; //接口请求地址
        $start_time = date('Y-m-d H:i:s',time());//请求时间
        $result = $this->post($url, $data); //发送请求
        $end_time = date('Y-m-d H:i:s',time());//相应时间
        $res = json_decode($result['res'], true); //解析返回结果
//        dd($url,$data,$result,$res);
        \Log::channel('sifang_pay_send')->info('诚易通支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
        //判断请求是否成功
        if ($result['responseCode'] != 200 || $res['rspCode'] != self::SUCCESS || !$cty->payVerify($res['data'])){
            return false;
        }
        //保存四方返回的数据
        $order->return_data = $result['res'] ?? '';
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
        try{
            //解析接口返回的数据
            $return_data = json_decode($order->return_data, true);
            $rspCode = $return_data['rspCode'] ?? 0;
            if($rspCode == 1){
                //扫码支付生成二维码
                $options = new QROptions([
                    'version'      => 7,//版本号
                    'outputType'   => QRCode::OUTPUT_IMAGE_JPG,
                    'eccLevel'     => QRCode::ECC_L,//错误级别
                    'scale'        => 10,//像素大小
                    'imageBase64'  => false,//是否将图像数据作为base64或raw来返回
                ]);
                header('Content-type: image/jpeg');
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
                header('Access-Control-Allow-Headers:x-requested-with,content-type');
                echo (new QRCode($options))->render($return_data['data']['r6_qrcode']);die;
            }else{
                return '支付请求错误';
            }
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('诚易通订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
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
        echo 'ok';
        die;
    }

}