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

class FirstPay extends PayAbstract
{

	const NAME = '第一支付';

	const CONFIG = [
		'gateway' => '支付网关',
		'mch_id'  => '商户APPID',
		'mch_key' => '商户APPKEY',
	];

	//支付通道配置
	const APIS = [
		self::WECHAT_QRCODE => [
			'FIRST_WXSCAN' => self::NAME . '微信扫码',
		],
		self::WECHAT_H5 => [
			'FIRST_WXH5'     => self::NAME . '微信H5',
		],
		self::ALIPAY_QRCODE => [
			'FIRST_ALISCAN' => self::NAME . '支付宝扫码',
		],
		self::ALIPAY_H5 => [
			'FIRST_ALIH5'     => self::NAME . '支付宝H5',
		],
        self::YUNSHANFU => [
            'FIRST_YUNSHANFU'     => self::NAME . '云闪付',
        ],
        self::UNIONPAY_QRCODE => [
            'FIRST_UNIONSCAN'     => self::NAME . '银联扫码',
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
			$sign = $callback_data['sign'] ?? '';
			if(!$sign){
				\Log::channel('sifang_pay_callback')->info('第一支付回调错误提示：返回'.json_encode($callback_data));
				return false;
			}
            $key = $this->sdk_config['mch_key'] ?? '';
			$selfSign = NormalSignature::encrypt($callback_data,'sign',$key);
			if ($selfSign != $sign) {
				\Log::channel('sifang_pay_callback')->info('第一支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
				return false;
			}
			$money = $callback_data['realAmount'];
			$order = PaymentOrder::where('order_no', $callback_data['orderId'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
			if ($order && $order->amount == $money) {
				$order->third_order_no = $callback_data['platOrderId'] ?? ''; //平台订单号
                $order->third_created_time = date('Y-m-d H:i:s',strtotime($callback_data['time'] ?? time()));
				$order->callback_data = json_encode($original, true);
				if ($order->thirdAddCoins()) {
					\Log::channel('sifang_pay_callback')->info('第一支付回调成功提示：用户金币充值成功');
					return true;
				}else{
					\Log::channel('sifang_pay_callback')->info('第一支付回调错误提示：用户金币充值失败');
					return false;
				}
			}
			return false;
		}catch (\Exception $exception){
			\Log::channel('sifang_pay_callback')->info('第一支付回调错误提示：系统异常'.$exception->getMessage());
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

	const SUCCESS = '00';//成功

	/**
	 * 请求四方订单方法
	 *
	 * @param PaymentOrder $order
	 *
	 * @return bool
	 */
	public function send(PaymentOrder &$order): bool
	{
		//通道转换
		switch ($order->provider->provider_key){
			case 'FIRST_WXH5':
				$provider_key  = '3';
				break;
			case 'FIRST_WXSCAN':
				$provider_key  = '10';
				break;
			case 'FIRST_ALISCAN':
				$provider_key  = '9';
				break;
			case 'FIRST_ALIH5':
				$provider_key  = '2';
				break;
            case 'FIRST_YUNSHANFU':
                $provider_key  = '24';
                break;
            case 'FIRST_UNIONSCAN':
                $provider_key  = '19';
                break;
			default:
				$provider_key = '10';
		}
        $key = $this->sdk_config['mch_key'] ?? '';
		//整理订单数据
		$data         = [
			'amount'        => $order->amount,//订单金额
			'payType'       => $provider_key,
			'appId'          => $this->sdk_config['mch_id'] ?? '', //设置配置参数
			'clientUrl'  => $this->sdk_config['callback'] ?? '',
			'notifyUrl'  => $this->sdk_config['callback'] ?? '',
			'orderId'     => $order->order_no,
			'time'   => date('Y-m-d H:i:s'),
            'remark'     => 'first_pay'
		];
		$data['sign'] = NormalSignature::encrypt($data,'sign',$key); //生成签名
		//保存四方返回的数据
		$order->return_data = json_encode($data);
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
        try{
            $data = json_decode($order->return_data, true);
            $url = $this->sdk_config['gateway'].'/app/pay'; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('FromSubmit', ['order'=>$data, 'action'=>$url,'title' => '第一支付','amount' => $data['amount']]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('第一支付请求异常:'.$exception->getMessage());
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

}