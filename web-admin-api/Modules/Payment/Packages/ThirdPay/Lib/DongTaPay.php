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

class DongTaPay extends PayAbstract
{

    const NAME = '东泰支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户号',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::ALIPAY_H5 => [
            'DONGTA_ALIPAY_H5'  => self::NAME . '支付宝H5',
            'DONGTA_ALIPAY_H5_REDIRECT'  => self::NAME . '支付宝H5直连',
        ],
        self::WECHAT_H5 => [
            'DONGTA_WECHAT_H5'  => self::NAME . '微信H5',
        ],
        self::UNIONPAY_QRCODE => [
            'DONGTA_UNIONPAY_QRCODE'  => self::NAME . '银联H5',
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
                \Log::channel('sifang_pay_callback')->info('东泰支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            $signData = [
                'tradeResult' => $callback_data['tradeResult'],
                'mchId' => $callback_data['mchId'],
                'mchOrderNo' => $callback_data['mchOrderNo'],
                'oriAmount' => $callback_data['oriAmount'],
                'amount' => $callback_data['amount'],
                'orderDate' => $callback_data['orderDate'],
                'orderNo' => $callback_data['orderNo'],
            ];
            $selfSign = strtolower(NormalSignature::encrypt($signData,'',"&key=".$key)); //生成签名
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('东泰支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($original));
                return false;
            }
            $money = $callback_data['amount'];
            $status = $callback_data['tradeResult'];
            $order = PaymentOrder::where('order_no',$callback_data['mchOrderNo'])->first(); //查询订单
            if(!$order){
                \Log::channel('sifang_pay_callback')->info('东泰支付回调错误提示：该订单未找到，请求IP：'.getIp());
                return false;
            }
            if($order->payment_status == PaymentOrder::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('东泰支付回调成功提示：该订单已支付，请求IP：'.getIp());
                return true;
            }
            if ($order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['orderNo'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('东泰支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('东泰支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            \Log::channel('sifang_pay_callback')->info('东泰支付回调错误提示：用户金币充值失败，金额为'.$money);
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('东泰支付回调错误提示：系统异常'.$exception->getMessage());
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
                case 'DONGTA_ALIPAY_H5':
                    $provider_key  = '021';
                    break;
                case 'DONGTA_WECHAT_H5':
                    $provider_key  = '020';
                    break;
                case 'DONGTA_UNIONPAY_QRCODE':
                    $provider_key  = '008';
                    break;
                case 'DONGTA_ALIPAY_H5_REDIRECT':
                    $provider_key  = '014';
                    break;
                default:
                    $provider_key  = '021';
            }
            $key = $this->sdk_config['mch_key'] ?? '';
            //整理订单数据
            $data         = [
                'mch_id'        => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'trade_amount'  => $order->amount, //订单金额
                'mch_order_no'  => $order->order_no,
                'notify_url'    => $this->sdk_config['callback'] ?? '',
                'pay_type'      => $provider_key,
                'order_date'    => date('Y-m-d H:i:s'),
                'goods_name'    => 'dongtaiPay',
            ];
            //生成签名
            $data['sign'] = strtolower(NormalSignature::encrypt($data,'',"&key=".$key));
            $data['sign_type']  = 'MD5';
            //保存四方返回的数据
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('恒玖支付请求异常:'.$exception->getMessage());
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
            return $this->show('FromSubmit', ['order'=>$data, 'action'=>$url,'title' => '东泰支付','amount' => $data['trade_amount']]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('东泰支付请求异常:'.$exception->getMessage());
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
