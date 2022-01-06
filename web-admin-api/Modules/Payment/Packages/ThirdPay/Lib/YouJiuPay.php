<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 17:34
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class YouJiuPay extends PayAbstract
{
    const NAME = '游久';//四方名称
    const SUCCESS = '00';//四方支付成功异步返回标识
    //商户信息配置
    const CONFIG = [
        'gateway'      => '支付网关',
        'pay_memberid' => '商户号',
        'pay_key'      => '密钥',
    ];
    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            '902' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            '901'     => self::NAME . '微信公众号',
        ],
        self::ALIPAY_QRCODE => [
            '903' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            '904'     => self::NAME . '支付宝h5',
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
    public function send(PaymentOrder &$order) : bool
    {
        try{
            //需要签名的数据
            $data = [
                'pay_memberid'      => $this->sdk_config['pay_memberid'] ?? '',//商户号
                'pay_orderid'       => $order->order_no,//订单号
                'pay_amount'        => $order->amount,//订单金额
                'pay_applydate'     => date('Y-m-d H:i:s', strtotime($order->created_at)),//提交时间
                'pay_bankcode'      => $order->provider->provider_key,//银行编码
                'pay_notifyurl'     => $this->sdk_config['callback'] ?? '',//异步通知地址
                'pay_callbackurl'   => $this->sdk_config['callback'] ?? '',//页面跳转返回地址
            ];
            //MD5签名
            $data['pay_md5sign'] = strtoupper(NormalSignature::encrypt($data, 'sign', '&key=' . $this->sdk_config['pay_key']));
            //不需要签名数据
            $data['pay_productname'] = '用户充值';//商品名称
            //可选的附加字段
            $data['pay_attach'] = json_encode(['user_id' => $order->user_id, 'order_no' => $order->order_no]);
            //保持四方请求数据，然后用于表单方式提交
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('游久订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('游久订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //获取需要加入签名的数据
            $callback_data['memberid']              = request('memberid');
            $callback_data['orderid']               = request('orderid');
            $callback_data['transaction_id']        = request('transaction_id');
            $callback_data['amount']                = request('amount');
            $callback_data['datetime']              = request('datetime');
            $callback_data['returncode']            = request('returncode');
            //对应的签名
            $sign                                   = request('sign');
            //验签
            ksort($callback_data);
            reset($callback_data);
            $md5str = "";
            foreach ($callback_data as $key => $val) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
            $selfsign = strtoupper(md5($md5str . "key=" . $this->sdk_config['pay_key']));
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('游久回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }

            //验证订单是否支付成功
            if ($callback_data['returncode'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('游久回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('游久回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount != $callback_data['amount']){
                \Log::channel('sifang_pay_callback')->info('游久回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;//订单状态改为支付成功
            $order->third_order_no = $callback_data['transaction_id'];//游久返回的流水号
            $order->success_time   = date('Y-m-d H:i:s', strtotime($callback_data['datetime']) ?? time());//游久的订单时间
            $order->callback_data   = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('游久回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('游久回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order)
    {
        try{
            //解析接口请求的数据
            $data = json_decode($order->return_data, true);
            $url          = $this->sdk_config['gateway']; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('YoujiuPay', ['order'=>$data, 'action'=>$url]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('游久订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}