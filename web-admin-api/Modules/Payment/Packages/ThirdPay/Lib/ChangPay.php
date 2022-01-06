<?php
/*
 |--------------------------------------------------------------------------
 | 畅支付sdk
 |--------------------------------------------------------------------------
 | Notes:
 | Class ChangPay
 | User: Administrator
 | Date: 2019/7/11
 | Time: 17:41
 |
 |  * @return
 |  |
 |
 */

namespace Modules\Payment\Packages\ThirdPay\Lib;

use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemLog;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Signature\NormalSignature;

class ChangPay extends PayAbstract
{
    const NAME = '畅支付';//四方名称
    const SUCCESS = 2;//支付异步返回成功状态值
    const FAILS = 1;//支付异步返回失败状态值
    //商户信息配置
    const CONFIG = [
        'gateway' => '支付网关',
        'mch_id'  => '商户id',
        'mch_key' => '商户密钥',
    ];
    //商户返回状态码对应提示
    const ERROR = [
        1   => '请求成功',
        100 => '缺少必要的参数',
        101 => '检验参数错误',
        102 => '找不到对应的商户',
        103 => '订单号不存在',
        110 => '查询异常',
        111 => '请求方法异常',
        112 => '没有可用支付通道',
        113 => '系统API异常',
        114 => '系统下单异常或支付通道异常',
    ];
    //支付通道
    const APIS = [
        self::WECHAT_QRCODE => [
            '/api/v1/wx_qrcode.api' => self::NAME . '微信扫码',
        ],
        self::WECHAT_H5 => [
            '/api/v1/wx_h5.api'     => self::NAME . '微信h5',
        ],
        self::ALIPAY_QRCODE => [
            '/api/v1/ali_qrcode.api' => self::NAME . '支付宝扫码',
        ],
        self::ALIPAY_H5 => [
            '/api/v1/ali_h5.api'     => self::NAME . '支付宝h5',
        ],
        self::YUNSHANFU => [
            '/api/v1/yunsf.api'     => self::NAME . '云闪付',
        ]
    ];

    public static function name()
    {
        return self::NAME;
    }

    public static function apis()
    {
        return self::APIS;
    }

    public static function config()
    {
        return self::CONFIG;
    }

    public function send(PaymentOrder &$order): bool
    {
        try{
            //整理订单数据
            $data = [
                'sign_type'  => 'md5',
                'mch_id'     => $this->sdk_config['mch_id'] ?? '', //设置配置参数
                'mch_order'  => $order->order_no,
                'amt'        => $order->amount * 1000,//订单金额1元等于1000厘
                'remark'     => $order->nickname . '(游戏ID:' . $order->game_id . ')充值',
                'created_at' => strtotime($order->created_at),
                'client_ip'  => getIp(),//获取客户端ip
                'notify_url' => $this->sdk_config['callback'] ?? '',
                'mch_key'    => $this->sdk_config['mch_key'] ?? '',
                'call'       => json_encode(['user_id' => $order->user_id, 'order_no' => $order->order_no]),
            ];
            $url          = $this->sdk_config['gateway'] . $order->provider->provider_key; //拼装请求地址
            $data['sign'] = NormalSignature::encrypt($data, 'sign'); //生成签名
            unset($data['mch_key']); //文档要求去除该项
            $start_time = date('Y-m-d H:i:s',time());//请求时间
            $result = $this->post($url, $data); //发送请求
            $end_time = date('Y-m-d H:i:s',time());//相应时间
            $res = json_decode($result['res'], true); //解析返回结果
            //dd($url,$data,$result);
            \Log::channel('sifang_pay_send')->info('请求时间：'.$start_time.'响应时间：'.$end_time.'请求地址：'.$url.'请求数据：'.json_encode($data).'请求返回数据'.json_encode($result));
            //判断请求是否成功
            if ($result['responseCode'] != 200 || $res['code'] != 1){
                return false;
            }
            $order->return_data        = $result['res'] ?? ''; //保存四方返回的数据
            $order->third_order_no     = $res['data']['mch_order'] ?? ''; //保存四方返回的订单好
            $order->third_created_time = date('Y-m-d H:i:s', $res['data']['created_at'] ?? time()); //设置四方生成订单的时间
            return true; //不需要save，组件会自动save
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('畅支付订单支付请求异常:'.$exception->getMessage());
            return false;
        }
    }

    public function callback(): bool
    {
        try{
            //回调时间
            \Log::channel('sifang_pay_callback')->info('畅支付订单回调时间:'.date('Y-m-d H:i:s',time()).'返回数据为：'.json_encode(request()->all()));
            //可选参数检测
            if (!request()->has(['call'])) {
                \Log::channel('sifang_pay_callback')->info('畅支付回调错误提示：缺少call参数');
            }
            //验证签名
            $callback_data            = request()->all();
            $callback_data['mch_key'] = $this->sdk_config['mch_key'];
            $sign                     = $callback_data['sign'];
            if (NormalSignature::encrypt($callback_data, 'sign') != $sign){
                \Log::channel('sifang_pay_callback')->info('畅支付回调错误提示：签名有误');
                return false;
            }
            //验证订单是否支付成功
            if ($callback_data['status'] != self::SUCCESS){
                \Log::channel('sifang_pay_callback')->info('畅支付回调错误提示：订单支付未成功');
                return false;
            }
            //解析call字段
            $call_data = json_decode(request('call'), true);
            //后台订单查询
            $model = PaymentOrder::where('order_no', $callback_data['mch_order'])->where('payment_status', PaymentOrder::WAIT); //查询订单
            if (isset($call_data['user_id']) && isset($call_data['order_no'])) {
                $model->where('user_id', $call_data['user_id'])->where('order_no', $call_data['order_no']);
            }
            $order = $model->first();
            if (!$order){
                \Log::channel('sifang_pay_callback')->info('畅支付回调错误提示：后台订单不存在');
                return false;
            }
            //验证充值金额和四方返回金额是否一致
            if ($order->amount * 1000 != $callback_data['mch_amt']){
                \Log::channel('sifang_pay_callback')->info('畅支付回调错误提示：充值金额不符');
                return false;
            }
            //金币充值
            $order->payment_status = PaymentOrder::SUCCESS;
            $order->success_time   = date('Y-m-d H:i:s', $callback_data['success_at'] ?? time());
            $order->callback_data   = json_encode($callback_data, true);
            if (!$order->thirdAddCoins()) {
                \Log::channel('sifang_pay_callback')->info('畅支付回调错误提示：用户金币充值失败');
                return false;
            }
            return true;
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_callback')->info('畅支付回调错误提示：系统异常'.$exception->getMessage());
            return false;
        }
    }

    public function view(PaymentOrder $order)
    {
        $return_data = json_decode($order->return_data, true);
        //h5链接跳转支付
        if (isset($return_data['data']['jump_url']) && $return_data['data']['jump_url'] != '') {
            return redirect($return_data['data']['jump_url']);
        }
        //扫码支付
        return $this->show('ChangPay', ['return'=>$return_data]);
    }

    public function queryOrder(PaymentOrder $order)
    {
        //四方主动通知，这边暂时不做
        return true;
    }

}
