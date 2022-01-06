<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 14:06
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class HuiFuTongPay extends PayAbstract
{
    const NAME = '汇付通';//四方名称
    const SUCCESS = 1;//已支付
    //商户信息配置
    const CONFIG = [
        'gateway'        => '支付网关',
        'memberid'       => '商户号',
        'pay_key'        => '密钥'
    ];
    //支付通道配置
    const APIS = [
        self::WECHAT_QRCODE => [
            'HFT_WXSCAN' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            'HFT_WXH5' => self::NAME . '微信H5',
        ],
        self::ALIPAY_QRCODE => [
            'HFT_ALISCAN' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            'HFT_ALIH5' => self::NAME . '支付宝H5',
        ],
    ];

    //支付通道对应转换
    const HFT_WXSCAN = 'WXSCAN';//微信扫码
    const HFT_WXH5 = 'WXH5';//微信H5
    const HFT_ALISCAN = 'ALISCAN';//支付宝扫码
    const HFT_ALIH5 = 'ALIH5';//支付宝H5

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
            //通道转换
            switch ($order->provider->provider_key){
                case 'HFT_WXSCAN':
                    $provider_key  = self::HFT_WXSCAN;
                    break;
                case 'HFT_WXH5':
                    $provider_key  = self::HFT_WXH5;
                    break;
                case 'HFT_ALISCAN':
                    $provider_key  = self::HFT_ALISCAN;
                    break;
                case 'HFT_ALIH5':
                    $provider_key  = self::HFT_ALIH5;
                    break;
                default:
                    $provider_key  = self::HFT_WXSCAN;
            }
            //需要加入签名的数据
            $data['amount']         =   number_format($order->amount,2);//充值金额（单位为元，必须为两位小数）
            $data['orderNo']        =   $order->order_no;//商户订单号
            $data['bank']           =   $provider_key;//银行代码（详见银行代码，不填时将跳转到收银台，不填时此字段不参与加密）
            $data['merchantNo']     =   $this->sdk_config['memberid'] ?? '';//商户号（系统分配唯一商户号）
            $data['name']           =   '用户充值';//商品名称
            $data['returnUrl']      =   $this->sdk_config['callback'] ?? '';//跳转地址
            $data['notifyUrl']      =   $this->sdk_config['callback'] ?? '';//通知地址
            $data['version']        =   '2.0';//版本号(1.0 和 2.0签名方法不一样)
            //签名
            $data["sign"] = NormalSignature::signature($data,'#'.$this->sdk_config['pay_key'],false);
            //不加入签名的字段
            $data['count']          =   '1';//商品数量
            //保存四方请求的数据
            $order->return_data = json_encode($data);
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('汇付通订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('汇付通订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //验证签名
            $callback_data['amount']            = request('amount');//充值金额（单位为元，必须为两位小数）
            $callback_data['transactionNo']     = request('transactionNo');//平台订单号
            $callback_data['orderNo']           = request('orderNo');//商户订单号（商户系统唯一订单编号）
            $callback_data['merchantNo']        = request('merchantNo');//商户号
            $callback_data['payStatus']         = request('payStatus');//支付状态：0-未支付,1-已支付,2-已关闭
            $callback_data['createdTime']       = request('createdTime');//创建时间，格式：yyyyMMddHHmmss
            $callback_data['paidTime']          = request('paidTime');//到账时间，格式：yyyyMMddHHmmss
            //对应的签名
            $sign                               = request('sign');//MD5签名结果
            //签名
            $selfsign = NormalSignature::signature($callback_data,'#'.$this->sdk_config['pay_key']);
            if ($sign != $selfsign) {
                \Log::channel('sifang_pay_callback')->info('汇付通回调错误提示：签名有误，内部签名：'.$selfsign.'返回数据：'.json_encode($callback_data));
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['payStatus'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('汇付通回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['orderNo'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('汇付通回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount != $callback_data['amount']){
                \Log::channel('sifang_pay_callback')->info('汇付通回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->third_order_no = $callback_data['transactionNo'];//汇付通返回的流水号
            $order->third_created_time = $callback_data['createdTime'];
            $order->success_time   =  $callback_data['paidTime'];
            $order->callback_data   = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('汇付通回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('汇付通回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order): string
    {
        try{
            //解析接口请求的数据
            $data = json_decode($order->return_data, true);
            $url          = $this->sdk_config['gateway']; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('HuiFuTongPay', ['order'=>$data, 'action'=>$url]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('汇付通订单支付请求异常:'.$exception->getMessage());
            return '系统异常';
        }
    }

    public function queryOrder(PaymentOrder $order)
    {
        return '';
    }
}