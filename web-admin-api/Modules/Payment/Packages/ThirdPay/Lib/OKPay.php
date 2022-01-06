<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2019/9/9
 * Time: 9:58
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class OKPay extends PayAbstract
{

    const NAME = 'OK付';

    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户APPID',
        'mch_key' => '商户APPKEY',
    ];

    const APIS = [
        self::WECHAT_H5 => [
            'OK_WX' => self::NAME . '微信支付',
        ],
        self::ALIPAY_H5 => [
            'OK_ZFB' => self::NAME . '支付宝支付',
        ],
    ];

    /**
     * 处理回调
     * @return bool
     */
    public function callback(): bool
    {
        try{
            $data = request()->all();
            $b_data = base64_decode($data['data']);
            $callback_data = json_decode($b_data, true);
            $sign = $callback_data['sign'] ?? '';
            if(!$sign){
                \Log::channel('sifang_pay_callback')->info('OK付回调错误提示：返回'.$b_data);
                return false;
            }
            $callback_data['appkey'] = $this->sdk_config['mch_key'];
            unset($callback_data['sign']);
            $selfsign = strtolower($this->encrypt($callback_data, 'sign_type'));
            if ($selfsign != $sign) {
                \Log::channel('sifang_pay_callback')->info('OK付回调错误提示：签名有误,内部签名：' . $selfsign . '返回数据：' . json_encode($callback_data));
                return false;
            }
            $money = $callback_data['money'];
            $order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
            if ($order && $order->amount == $money) {
                $order->payment_status = PaymentOrder::SUCCESS;
                $order->success_time = date('Y-m-d H:i:s');
                $order->callback_data = json_encode($callback_data, true);
                if ($order->thirdAddCoins()) {
                    \Log::channel('sifang_pay_callback')->info('OK付回调成功提示：用户金币充值成功');
                    return true;
                }else{
                    \Log::channel('sifang_pay_callback')->info('OK付回调错误提示：用户金币充值失败');
                    return false;
                }
            }
            return false;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('OK付回调错误提示：系统异常'.$exception->getMessage());
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
        //整理订单数据
        $data         = [
            'money'        => $order->amount,//订单金额
            'paycode'       => $order->provider->provider_key == 'OK_WX' ? 1 : 2,
            'appid'          => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'appkey'        => $this->sdk_config['mch_key'] ?? '',
            'notifyurl'  => $this->sdk_config['callback'] ?? '',
            'returnurl'  => $this->sdk_config['callback'] ?? '',
            'membername'    => $order->user_id,
            'orderid'     => $order->order_no,
            'goodsname'   => 'chongzhi',
            'orderperiod'=> '10',
            'timestamp'  => time(),
            'sign_type'  => 'MD5',
            'remark'     => 'remark'
        ];
        $data['sign'] = strtolower($this->encrypt($data, 'sign_type')); //生成签名
        unset($data['appkey']);
        $url          = $this->sdk_config['gateway']; //接口请求地址
        $start_time = date('Y-m-d H:i:s',time());//请求时间
        $result = $this->post($url, $data); //发送请求
        $end_time = date('Y-m-d H:i:s',time());//相应时间
        $res = json_decode($result['res'], true); //解析返回结果
        //dd($url,$data,$result,$res);
        \Log::channel('sifang_pay_send')->info('OK付请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据：'.$result['res']);
        //判断请求是否成功
        if ($result['responseCode'] != 200 || $res['state'] != self::SUCCESS){
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

    public function encrypt(array $data, string $without = 'sign')
    {
        ksort($data);
        $params = '';
        foreach ($data as $key => $value) {
            if ($value === '' || $value == null || $key == $without) {
                continue;
            }
            $params .= $key . '=' . urlencode($value) . '&';
        }
        $params = rtrim($params, '&');
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $params = stripslashes($params);
        }
        return md5($params);
    }
}