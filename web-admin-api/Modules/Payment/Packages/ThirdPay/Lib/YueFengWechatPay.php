<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 14:06
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class YueFengWechatPay extends PayAbstract
{
    const NAME = '悦风微信';//四方名称
    const SUCCESS = '00';//四方支付成功异步返回标识
    //商户信息配置
    const CONFIG = [
        'wx_gateway'        => '微信支付网关',
        'wx_memberid'       => '微信商户号',
        'wx_key'            => '微信密钥'
    ];
    //通道配置
    const PROVIDER_CODE = '902';
    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'wx_qrcode_902' => self::NAME . '扫码',
        ],
        self::WECHAT_H5 => [
            'wx_h5_902' => self::NAME . 'H5',
        ]
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
            //需要加入签名的数据
            $data = [
                'pay_memberid'      => $this->sdk_config['wx_memberid'] ?? '',//商户号
                'pay_orderid'       => $order->order_no,//订单号
                'pay_applydate'     => date('Y-m-d H:i:s', strtotime($order->created_at)),//订单时间
                'pay_bankcode'      => self::PROVIDER_CODE,//银行编码
                'pay_notifyurl'     => $this->sdk_config['callback'] ?? '',//服务器通知地址
                'pay_callbackurl'   => $this->sdk_config['callback'] ?? '',//页面返回地址
                'pay_amount'        => $order->amount,//金额
            ];
            //签名
            $data["pay_md5sign"] = NormalSignature::signature($data,'&key='.$this->sdk_config['wx_key']);
            $data['pay_productname'] = '用户充值';
            //保存四方请求的数据
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('悦风微信订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('悦风微信订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //验证签名
            $callback_data['memberid']         = request('memberid');//商户编号
            $callback_data['orderid']          = request('orderid');//商户订单号
            $callback_data['amount']           = request('amount');//订单金额
            $callback_data['transaction_id']   = request('transaction_id');//交易流水号
            $callback_data['datetime']         = request('datetime');//交易时间
            $callback_data['returncode']       = request('returncode');//交易状态
            //对应的签名
            $sign                              = request('sign');
            //签名
            $selfsign = NormalSignature::signature($callback_data,'&key='.$this->sdk_config['wx_key']);
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('悦风微信回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['returncode'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('悦风微信回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('悦风微信回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount != $callback_data['amount']){
                \Log::channel('sifang_pay_callback')->info('悦风微信回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['transaction_id'];//悦风微信返回的流水号
            $order->success_time   = $callback_data['datetime'];
            $order->callback_data   = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('悦风微信回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('悦风微信回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        try{
            $data = json_decode($order->return_data, true);
            $url          = $this->sdk_config['wx_gateway']; //拼装请求地址
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $str = '悦风微信支付请求时间：'.$start_time.'请求地址：'.$url.'请求数据：'.json_encode($data);
            if (json_decode($result['res'], true)){
                $str .= '请求返回数据：'.$result['res'];
            }
            \Log::channel('sifang_pay_send')->info($str);
            //判断请求是否成功
            if ($result['responseCode'] != 200){
                return '支付请求错误'.$result['responseCode'];
            }
            //如果返回json，并提示错误
            return $result['res'];
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('悦风微信订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}