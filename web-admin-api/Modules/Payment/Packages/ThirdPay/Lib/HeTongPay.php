<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 14:06
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class HeTongPay extends PayAbstract
{
    const NAME = '和通';//四方名称
    const SUCCESS = 'S';//四方支付成功异步返回标识
    //商户信息配置
    const CONFIG = [
        'gateway'        => '支付网关',
        'memberid'       => '商户号',
        'pay_key'        => '密钥'
    ];
    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'WECHAT_QRCODE_PAY' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'WECHAT_WAP_PAY' => self::NAME . '微信WAP',
        ],
        self::ALIPAY_QRCODE => [
            'ALIPAY_QRCODE_PAY' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'ALIPAY_WAP_PAY' => self::NAME . '支付宝WAP',
        ],
        self::YUNSHANFU => [
            'UNIONPAY_QRCODE_PAY' => self::NAME.'云闪付扫码'
        ]
    ];
    //通道类别分类定义，由于该四方不同通道返回格式不同
    const SCAN_TYPE = ['WECHAT_QRCODE_PAY','ALIPAY_QRCODE_PAY','UNIONPAY_QRCODE_PAY'];//扫码类，返回json类型
    const WAP_TYPE = ['WECHAT_WAP_PAY','ALIPAY_WAP_PAY'];//wap类，需form提交

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

    //表单提交，即收银台模式
    public function send(PaymentOrder &$order): bool
    {
        try{
            //需要加入签名的数据
            $data = [
                'partner'           => $this->sdk_config['memberid'] ?? '',//商户号
                'out_trade_no'      => $order->order_no,//订单号
                'total_fee'         => $order->amount * 100,//单位：分
                'notify_url'        => $this->sdk_config['callback'] ?? '',//服务器通知地址
                'payment_type'      => $order->provider->provider_key,//支付类型代码
                'timestamp'         => date('Y-m-d H:i:s', strtotime($order->created_at)),//订单时间
            ];
            //签名
            $data["sign"] = NormalSignature::signature($data,$this->sdk_config['pay_key']);
            //保存四方提交的数据
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('和通订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('和通订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //验证签名
            $callback_data['code']              = request('code');//接口状态码
            $callback_data['partner']           = request('partner');//商户在平台的商户编号
            $callback_data['out_trade_no']      = request('out_trade_no');//商户订单号
            $callback_data['total_fee']         = request('total_fee');//单位：分
            $callback_data['service_charge']    = request('service_charge');//单位：分
            $callback_data['state']             = request('state');//订单状态： W 待支付，S 已支付
            $callback_data['trade_no']          = request('trade_no');//平台订单号
            $callback_data['timestamp']         = request('timestamp');//发送请求的时间 格式”yyyy-MM-dd HH:mm:ss”
            //对应的签名
            $sign                               = request('sign');//MD5签名结果
            //签名
            $selfsign = NormalSignature::signature($callback_data,$this->sdk_config['pay_key']);
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('和通回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['state'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('和通回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['out_trade_no'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('和通回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount * 100 != $callback_data['total_fee']){
                \Log::channel('sifang_pay_callback')->info('和通回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['trade_no'];//和通返回的流水号
            $order->third_created_time = $callback_data['timestamp'];
            $order->success_time   = date('Y-m-d H:i:s',time());
            $order->callback_data   = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('和通回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('和通回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        //解析订单存放的四方请求数据
        $data = json_decode($order->return_data, true);
        $url          = $this->sdk_config['gateway']; //接口请求地址
        //扫码方式和wap的form的提交方式的判断
        if (in_array($order->provider->provider_key,self::SCAN_TYPE)){
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            \Log::channel('sifang_pay_send')->info('和通支付请求时间：'.$start_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);

            $res = json_decode($result['res'], true); //解析返回结果
            //判断请求是否成功
            if ($result['responseCode'] == 200 && $res['code'] == 0){
                //自行生成二维码
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
                echo (new QRCode($options))->render($res['qrcode_url']);die;
            }
            //错误返回,如果有数据进行重定向
            if (isset($res['qrcode_url']) && $res['qrcode_url'] != ''){
                return redirect($res['qrcode_url']);
            }
            return $result['res'];
        }
        //wap的form表单提交，有客户端发送请求，四方302重定向，此处生成页面
        if (in_array($order->provider->provider_key,self::WAP_TYPE)){
            return $this->show('HeTongWapPay', ['order'=>$data, 'action'=>$url]);
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}