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

class SmallJuRenPay extends PayAbstract
{

    const NAME = '小巨人';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'app_id'  => '应用ID',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::ALIPAY_QRCODE => [
            'SJUREN_ALIPAY_QRCODE'  => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'SJUREN_ALIPAY_H5'  => self::NAME . '支付宝H5',
            'JUREN_ALIPAY_H5_ZHUANKA'  => self::NAME . '支付宝H5转卡',
        ],
        self::WECHAT_QRCODE => [
            'JUREN_WECHAT_QRCODE'  => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'JUREN_WECHAT_H5'  => self::NAME . '微信H5',
            'JUREN_WECHAT_H5_ZHUANKA'  => self::NAME . '微信H5转卡',
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
                \Log::channel('sifang_pay_callback')->info('小巨人支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $selfSign = strtoupper(NormalSignature::encrypt($callback_data,'sign','&key='.$key)); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('小巨人支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['amount'] / 100;

//	        $visual_order_no_string=explode('-', $callback_data['mchOrderNo']);
//	        list($head,$game_id,$order_id)=$visual_order_no_string;
            $order = PaymentOrder::where('order_no',$callback_data['mchOrderNo'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('小巨人支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('小巨人支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money) {
                $order->third_order_no = $callback_data['payOrderId']; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('小巨人支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('小巨人支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('小巨人支付回调错误提示：用户金币充值失败，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('小巨人支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = '2';//成功

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
                case 'JUREN_WECHAT_QRCODE':
                    $provider_key  = '8002';
                    break;
                case 'JUREN_WECHAT_H5':
                    $provider_key  = '8003';
                    break;
                case 'SJUREN_ALIPAY_QRCODE':
                    $provider_key  = '8006';
                    break;
                case 'SJUREN_ALIPAY_H5':
                    $provider_key  = '8007';
                    break;
                case 'JUREN_ALIPAY_H5_ZHUANKA' || 'JUREN_WECHAT_H5_ZHUANKA':
                    $provider_key  = '8022';
                    break;
                default:
                    $provider_key  = '8006';
            }
            $url = $this->sdk_config['gateway'];  //接口请求地址
            $key = $this->sdk_config['mch_key'] ?? '';
            $data         = [
                'productId'     => $provider_key,
                'clientIp'      => getIp(),
                'mchId'         => $this->sdk_config['mch_id'] ?? '',
                'appId'         => $this->sdk_config['app_id'] ?? '',
                'notifyUrl'     => $this->sdk_config['callback'] ?? '',
                'mchOrderNo'    => $order->order_no,
                'amount'        => $order->amount * 100, //订单金额
                'currency'      => 'cny',
                'subject'       => 'SJURENPay',
                'body'          => 'SJURENPay',
                'returnUrl'     => '',
                'extra'         => "SJURENPay",
            ];
            $data['sign'] = strtoupper(NormalSignature::encrypt($data,'sign','&key='.$key)); //生成签名
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url,$data); //发送请求
//            dd($data,$result);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('小巨人支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            $success = $res['retCode'] ?? '';
            if ($result['responseCode'] != 200 || $success != "SUCCESS"){
                return false;
            }
            //保存四方返回的数据
            $order->return_data = $result['res'] ?? '';
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('小巨人支付请求异常:'.$exception->getMessage());
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
        try{
            $data = json_decode($order->return_data, true);
            $data1 = json_decode($order->return_data, true);
            $image = $data['payParams']['payUrl'] ?? '';
            $amount = $order->amount ?? '';
            $order_no = $order->order_no ?? '';
            if(!$image || !$amount || !$order_no){
                return '参数丢失';
            }
            $pattern = '/<img[\s\S]*?src\s*=\s*[\\" | \'](.*?)[\\"|\'][\s\S]*?>/i';
            preg_match($pattern,$image,$data);
            $pic = $data[1] ?? '';
            if($pic){
                $file_name = md5(time().$order_no).'.jpg';
                $path = storage_path("app/public/code/");
                if(!file_exists($path)){
                    mkdir($path,0777,true);
                }
                $pic_data = $pic;
                if (strstr($pic,",")){
                    $im = explode(',',$pic);
                    $pic_data = $im[1];
                }
                file_put_contents($path.$file_name, base64_decode($pic_data));
                $qrCode = new \Zxing\QrReader($path.$file_name);
                $url = $qrCode->text();
                return $this->show('JuRenPay', ['image' => $pic, $image,'pay_url'=> $url,'amount' => $amount,'order_no' => $order_no]);
            }
            $jump = $data1['payParams']['payJumpUrl'] ?? '';
            if($jump){
                return redirect($jump);
            }
            return '请求超时，请重试';
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('巨人支付请求异常:'.$exception->getMessage());
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
        echo 'success';
        die;
    }


}
