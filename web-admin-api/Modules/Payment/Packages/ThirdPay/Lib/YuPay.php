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

class YuPay extends PayAbstract
{

    const NAME = '御支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::ALIPAY_H5 => [
            'YU_ALIPAY_H5'  => self::NAME . '支付宝H5',
        ],
        self::ALIPAY_QRCODE => [
            'YU_ALIPAY_QRCODE'  => self::NAME . '支付宝扫码',
        ],
        self::WECHAT_H5 => [
            'YU_WECHAT_H5'  => self::NAME . '微信H5',
        ],
        self::WECHAT_QRCODE => [
            'YU_WECHAT_QRCODE'  => self::NAME . '微信扫码',
        ],
        self::UNIONPAY_QRCODE => [
            'YU_UNIONPAY_QRCODE'  => self::NAME . '银联扫码',
        ]
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
                \Log::channel('sifang_pay_callback')->info('御支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $signData = [
                'status' => $callback_data['status'],
                'merchant_id' => $callback_data['merchant_id'],
                'source_order_id' => $callback_data['source_order_id'],
                'order_amount' => $callback_data['order_amount'],
                'payTime' => $callback_data['payTime'],
                'order_code' => $callback_data['order_code'],
                'goods_name' => $callback_data['goods_name'],
            ];
            $selfSign = $this->signature($key,$signData); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('御支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['order_amount'];
            $status = $callback_data['status'];
            $order = PaymentOrder::where('order_no',$callback_data['source_order_id'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('御支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('御支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['order_code'] ?? ''; //平台订单号
                $order->third_created_time = $callback_data['payTime'] ?? '';
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('御支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('御支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('御支付回调错误提示：用户金币充值失败，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('御支付回调错误提示：系统异常'.$exception->getMessage());
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
                case 'YU_ALIPAY_H5':
                    $provider_key  = '28';
                    break;
                case 'YU_ALIPAY_QRCODE':
                    $provider_key  = '27';
                    break;
                case 'YU_WECHAT_H5':
                    $provider_key  = '32';
                    break;
                case 'YU_WECHAT_QRCODE':
                    $provider_key  = '4';
                    break;
                case 'YU_UNIONPAY_QRCODE':
                    $provider_key  = '33';
                    break;
                default:
                    $provider_key  = '27';
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $data         = [
                'merchant_id'       => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'payment_way'       => $provider_key,
                'order_amount'      => $order->amount, //订单金额
                'source_order_id'   => $order->order_no,
                'goods_name'        => 'YuPay',
                'bank_code'         => 'ICBC',
                'client_ip'         => getIp(),
                'notify_url'        => $this->sdk_config['callback'] ?? '',
                'return_url'        => $this->sdk_config['callback'] ?? '',
            ];
            //生成签名
            $data['sign'] = $this->signature($key,$data);
            //保存四方返回的数据
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('御支付请求异常:'.$exception->getMessage());
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
            $url  = $this->sdk_config['gateway']; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('FromSubmit', ['order'=>$data, 'action'=>$url,'title' => '御支付','amount' => $data['order_amount']]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('御支付请求异常:'.$exception->getMessage());
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

    private function signature($key='',$params=[]){
        $params['token']=$key; //加入token
        ksort($params); //参数数组按键升序排列
        $clear_text='';    //将参数值按顺序拼接成字符串
        foreach ($params as $key=>$value){
          $clear_text .= $key.'='.$value.'&';
        }
        $clear_text = trim($clear_text,'&');
        $cipher_text=md5($clear_text); //计算md5 hash
        return $cipher_text;
    }

}
