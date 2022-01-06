<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/6
 * Time: 11:58
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;
use Modules\Payment\Packages\ThirdPay\Signature\RedBullSignature;

class RedBullPay extends PayAbstract
{

    const NAME = '红牛';
    const SUCCESS = 1; //交易成功
    const FAILS = 3;//交易失败

    const CONFIG = [
        'gateway'       => '支付网关',
        'brandNo'       => '商户编号',
        'userName'      => '商户端用户名',
        'private_key'   => '商户私钥',
        'public_key'    => '商户公钥'
    ];

    //商户返回状态码对应提示
    const STATUS_CODE = [
        0   => '尚未处理',
        1   => '交易成功',
        2   => '交易处理中',
        3   => '交易失败',
        4   => '交易审核中',
        99  => '交易异常'
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            '1102' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            '1202'     => self::NAME . '微信h5',
        ],
        self::ALIPAY_QRCODE => [
            '1101' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            '1201'     => self::NAME . '支付宝h5',
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

    /**
     * 请求四方订单方法
     *
     * @param PaymentOrder $order
     *
     * @return bool
     */
    public function send(PaymentOrder &$order): bool
    {
        header("content-Type: text/html; charset=UTF-8");
        try{
            //整理订单数据
            $data         = [
                'brandNo'       => $this->sdk_config['brandNo'] ?? '', //商户编号
                'clientIp'      => getIp(),//商户端用户端IP
                'orderNo'       => $order->order_no,//订单编号
                'price'         => $order->amount,//订单金额
                'serviceType'   => $order->provider->provider_key,//通道，服务类型
                'userName'      => $this->sdk_config['userName'],//商户端用户名
            ];
            //生成签名
            $data['signature'] = RedBullSignature::rsaSign(RedBullSignature::encrypt($data),$this->sdk_config['private_key']);
            //非签名的必填数据,签名方式
            $data['signType'] = "RSA-S";
            //可选数据
            $data['callbackUrl'] = $this->sdk_config['callback'] ?? '';
            $param = json_encode($data);
            $url = $this->sdk_config['gateway'];
            $start_time = date('Y-m-d H:i:s',time());//相应时间

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.strlen($param)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

            $res = curl_exec($ch);
            curl_close($ch);
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            dd($url,$data,$res);
            \Log::channel('sifang_pay_send')->info('红牛支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.$param.'请求返回数据：'.$res);
            //判断请求是否成功
            if ($res['code'] != 0){
                return false;
            }
            $order->return_data        = $res ?? ''; //保存四方返回的数据
            return true; //不需要save，组件会自动save
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('红牛订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            \Log::channel('sifang_pay_callback')->info('红牛订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //需要签名的数据
            $callback_data['actualPrice']   = request('actualPrice');//#.00	扣除手续费后的实际值
            //yyyyMMddHHmmss	交易完成时间 (2018-07-17 07:19:26.333)参与签名格式 与 回调格式 不同
            $callback_data['dealTime']      = date('YmdHis',strtotime(request('dealTime')));
            $callback_data['orderNo']       = request('orderNo');//订单编号
            $callback_data['orderStatus']   = request('orderStatus');//订单状态
            //yyyyMMddHHmmss	订单时间 (2018-07-17 07:19:25.910)参与签名格式 与 回调格式 不同
            $callback_data['orderTime']     = date('YmdHis',strtotime(request('orderTime')));
            $callback_data['price']         = request('price');//交易额，以此值上分
            $callback_data['tradeNo']       = request('tradeNo');//Red Bull Pay 交易订单编号
            //签名字串
            $signature                      = request('signature');
            //验签
            if(!RedBullSignature::rsaSign(RedBullSignature::encrypt($callback_data),$this->sdk_config['public_key'],$signature)){
                \Log::channel('sifang_pay_callback')->info('红牛回调错误提示：签名有误');
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['orderStatus'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('红牛回调错误提示：订单支付未成功');
                return false;
            }
            //后台订单查询
            $order = PaymentOrder::where('order_no', $callback_data['orderNo'])->where('payment_status', PaymentOrder::WAIT)->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('红牛回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount != $callback_data['price']){
                \Log::channel('sifang_pay_callback')->info('红牛回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status      = PaymentOrder::SUCCESS;//订单状态改为成功
            $order->third_created_time  = request('orderTime');//四方订单创建时间
            $order->success_time        = request('dealTime');//四方订单交易完成时间
            $order->third_order_no      = $callback_data['tradeNo'];//四方平台订单号
            $order->callback_data       = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('红牛回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('红牛回调错误提示：系统异常'.$exception->getMessage());
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
        $return_data = json_decode($order->return_data, true);
        if (isset($return_data['data']['payUrl']) && $return_data['data']['payUrl'] != '') {
            return redirect($return_data['data']['payUrl']); //交易位置, 将使用者导向 (Redirect) 至此位置
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
        return true;
    }
}
