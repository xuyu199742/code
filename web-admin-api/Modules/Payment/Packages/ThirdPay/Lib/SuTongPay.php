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

class SuTongPay extends PayAbstract
{

    const NAME = '速通支付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户APPID',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_QRCODE => [
            'ST_WX' => self::NAME . '微信扫码',
        ],
        self::ALIPAY_H5 => [
            'ST_ZFB' => self::NAME . '支付宝扫码',
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
            $sign = $callback_data['sign'] ?? '';
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('速通支付回调错误提示：返回'.json_encode($callback_data));
                return false;
            }
            $selfSign = strtolower(md5('appid='.$callback_data['appid'].'&order_no='.$callback_data['order_no'].'&out_order_no='.$callback_data['out_order_no'].'&amount='.$callback_data['amount'].'&appkey='.($this->sdk_config['mch_key'] ?? '')));
            if ($selfSign != $sign) {
                \Log::channel('sifang_pay_callback')->info('速通支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['amount'];
            $order = PaymentOrder::where('order_no', $callback_data['out_order_no'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money) {
                $order->third_order_no = $callback_data['order_no'] ?? ''; //平台订单号
                $order->callback_data = json_encode($original, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('速通支付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('速通支付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('速通支付回调错误提示：系统异常'.$exception->getMessage());
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

    const SUCCESS = 0;//成功

    /**
     * 请求四方订单方法
     *
     * @param PaymentOrder $order
     *
     * @return bool
     */
    public function send(PaymentOrder &$order): bool
    {
        //整理订单数据
        $data         = [
            'appid'          => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'out_order_no'     => $order->order_no,
            'amount'        => $order->amount,//订单金额
            'paytype'       => $order->provider->provider_key == 'ST_WX' ? 1 : 0,
            'appkey'        => $this->sdk_config['mch_key'] ?? '',
        ];
        $data['sign'] = strtolower(md5(urldecode(http_build_query($data)))); //生成签名
        $data['callbackUrl'] = $this->sdk_config['callback'] ?? '';
        unset($data['appkey']);
        $url          = $this->sdk_config['gateway']; //接口请求地址
        $start_time = date('Y-m-d H:i:s',time());//请求时间
        $result = $this->get($url, $data); //发送请求
        $end_time = date('Y-m-d H:i:s',time());//相应时间
        $res = json_decode($result['res'], true); //解析返回结果
        //dd($url,$data,$result,$res);
        \Log::channel('sifang_pay_send')->info('速通支付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
        //判断请求是否成功
        if ($result['responseCode'] != 200 || $res['code'] != self::SUCCESS){
            return false;
        }
        //保存四方返回的数据
        $order->return_data = $result['res'] ?? '';
        return true;
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
        //解析接口返回的数据
        $return_data = json_decode($order->return_data, true);
        return redirect($return_data['url']);
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


}