<?php
namespace Modules\Payment\Packages\ThirdPay\Lib;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\yida\PayDataBase;
use Modules\Payment\Packages\ThirdPay\Signature\yida\PayException;
use Modules\Payment\Packages\ThirdPay\Signature\yida\PayResults;
use Modules\Payment\Packages\ThirdPay\Signature\yida\PayUtil;

class YiDaPay extends PayAbstract
{
    const NAME = '益达';//四方名称
    const SUCCESS = '0000';//成功
    //商户信息配置
    const CONFIG = [
        'gateway'           => '支付网关',
        'mch_id'            => '商户号',
        'private_key'       => '私钥',
        'public_key'        => '公钥',
    ];

    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            '1' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            '7'     => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            '2' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            '8'     => self::NAME . '支付宝H5',
        ],
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

    //表单提交，即收银台模式
    public function send(PaymentOrder &$order): bool
    {
        try{
            //所有非空值字段均参与签名, 0也要参与签名
            $data['inputCharset']   = '1';//字符集,固定填1；1代表UTF-8
            $data['partnerId']      = $this->sdk_config['mch_id'] ?? '';//商户号
            $data['notifyUrl']      = $this->sdk_config['callback'] ?? '';//支付结果异步通知地址
            $data['returnUrl']      = $this->sdk_config['callback'] ?? '';//页面跳转同步通知页面路径
            $data['orderNo']        = $order->order_no;//商户订单号
            $data['orderAmount']    = strval($order->amount * 100);//商户金额,整型数字，单位是分，
            $data['orderCurrency']  = '156';//币种类型,固定填156;人民币
            $data['orderDatetime']  = date('YmdHis',time());//商户订单提交时间，日期格式：yyyyMMDDhhmmss，例如：20190218020120必须使用24小时格式
            $data['payMode']        = $order->provider->provider_key;//支付方式
            $data['subject']        = '用户充值';//交易名称
            $data['body']           = '用户充值';//订单描述
            $data['extraCommonParam'] = '';
            $data['bnkCd']          = '';
            $data['cardNo']         = '';
            $data['accTyp']         = '';
            $data['ip']             = getIp();//商户获取客户的使用ip地址，然后提交给平台，非server地址
            $data['signType']       = '1';//签名类型,1代表RSA

            //签名
            $inputObj = new PayDataBase();
            $inputObj->values = $data;
            $inputObj->SetSign($this->sdk_config['private_key']);
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $response = PayUtil::postCurl($inputObj->values, $this->sdk_config['gateway'], 30);
            \Log::channel('sifang_pay_send')->info('益达支付请求时间：'.$start_time.'返回数据：'.$response);
            //返回结果转数组（验签）
            $result = PayResults::init($response,false, $this->sdk_config['public_key']);
            //判断请求是否成功
            if ($result['errCode'] != self::SUCCESS){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $response;
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('益达订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        //如果返回成功则验证签名
        try {
            $callback_data = request()->all();
            \Log::channel('sifang_pay_callback')->info('益达订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode($callback_data));
            //验证签名
            $result = PayResults::InitFromArray($callback_data,true,$this->sdk_config['public_key']);
            if($result == false){
                \Log::channel('sifang_pay_callback')->info('益达回调错误提示：签名有误');
            }
            //验证订单是否支付成功
            if ($callback_data['result_code'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('益达回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['orderNo'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('益达回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount * 100 != $callback_data['orderAmount']){
                \Log::channel('sifang_pay_callback')->info('益达回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['tradeSeq'];//平台订单号
            $order->success_time   = date('Y-m-d H:i:s', strtotime($callback_data['payDatetime']) ?? time());
            $order->callback_data   = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('益达回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        } catch (\Exception $e){
            \Log::channel('sifang_pay_callback')->info('益达回调错误提示：系统异常'.$e->getMessage());
            return false;
        }

    }

    public function view(PaymentOrder $order)
    {
        //解析接口返回的数据
        $return_data = json_decode($order->return_data, true);
        if(!empty($return_data['qrCode'])){
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
            echo (new QRCode($options))->render($return_data['qrCode']);die;
        }else{
            //非扫码支付返回html
            echo $return_data['retHtml'];die;
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}