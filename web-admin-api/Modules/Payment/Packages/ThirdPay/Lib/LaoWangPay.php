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

class LaoWangPay extends PayAbstract
{

    const NAME = '老王支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '秘钥',
        'vector'  => 'VECTOR',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'LAOWANG_WX_H5' => self::NAME . '微信H5',
        ],
        self::ALIPAY_H5 => [
            'LAOWANG_ALIPAY_H5'  => self::NAME . '支付宝H5',
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
            $content = $callback_data['content'];
            if(!$content){
                \Log::channel('sifang_pay_callback')->info('老王支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $original = $this->decrypt($content);
            if (!$original) {
                \Log::channel('sifang_pay_callback')->info('老王支付回调错误提示：AES解密失败，返回数据：' . json_encode($original));
                return false;
            }
            $money = $original['amount'];
            $status = $original['status'];
            list($id,$user_id) = explode('o',$original['order_no']);
            $order = PaymentOrder::where('id',$id)->where('user_id',$user_id)->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('老王支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('老王支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money && $status != 'failed') {
                $order->third_order_no = $original['tx_no'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('老王支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('老王支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('老王支付回调错误提示：用户金币充值失败，回调状态为'.$status.'，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('老王支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = true;//成功

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
                case 'LAOWANG_WX_H5':
                    $provider_key  = 'weixin';
                    break;
                case 'LAOWANG_ALIPAY_H5':
                    $provider_key  = 'alipay';
                    break;
                default:
                    $provider_key  = 'alipay';
            }
            //整理订单数据
            $data         = [
                'return_url'  =>  $this->sdk_config['callback'] ?? '',
                'notify_url'   => $this->sdk_config['callback'] ?? '',
                'order_no'     => $order->id.'o'.$order->user_id,
                'ip'           => getIp(),
                'amount'       => number_format(floatval($order->amount), 2, '.', ''), //订单金额
                'gateway'      => $provider_key,
            ];
            $data['sign'] = $this->encrypt($data); //生成签名
            //保存四方返回的数据
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('老王支付请求异常:'.$exception->getMessage());
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
            $url = $this->sdk_config['gateway'].'/deposit/'.$this->sdk_config['mch_id'].'/mobile/forward';  //接口请求地址
            $data = json_decode($order->return_data, true);
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->_httpPost($url,$data); //发送请求
            //        dd($url,$data,$result);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('sifang_pay_send')->info('老王支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['ok'] != self::SUCCESS){
                return 'fail';
            }
            $method = $res['data']['method'];
            switch ($method){
                case 'get':
                    return redirect($res['data']['url']);
                    break;
                case 'post':
                    $url = $res['data']['url']; //拼装请求地址
                    //form方式提交，由客户端发送请求
                    return $this->show('FromSubmit', ['order'=>$data, 'action'=>$url,'title' => '老王支付','amount' => $order->amount]);
                    break;
                case 'qrcode':
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
                    echo (new QRCode($options))->render($res['data']['url']);die;
                    break;
            }
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('老王支付请求异常:'.$exception->getMessage());
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

    public function encrypt($data)
    {
        ksort($data);
        $vector = $this->sdk_config['vector'] ?? '';
        $key = $this->sdk_config['mch_key'] ?? '';
        $string = json_encode($data,JSON_UNESCAPED_SLASHES);
        $encrypt = @openssl_encrypt($string, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $vector);
        $data = base64_encode($encrypt);
        return $data;
    }

    public function decrypt($data)
    {
        $vector = $this->sdk_config['vector'] ?? '';
        $key = $this->sdk_config['mch_key'] ?? '';
        $decrypt = openssl_decrypt(base64_decode($data),'AES-256-CBC',$key,OPENSSL_RAW_DATA,$vector);
        $data = json_decode($decrypt,true);
        return $data;
    }

    public function _httpPost($url="" ,$requestData=array()){

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        //普通数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestData));
        $result       = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return array('responseCode' => $responseCode, 'res' => $result);
    }



}