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

class MiaoPay extends PayAbstract
{

	const NAME = '秒支付';

	const CONFIG = [
		'gateway' => '支付网关',
		'mch_id'  => '商户APPID',
		'mch_key' => '商户APPKEY',
	];

	//支付通道配置
	const APIS = [
		self::ALIPAY_QRCODE => [
            'MIAO_ALISCAN' => self::NAME . '支付宝扫码',
		],
		self::ALIPAY_H5 => [
			'MIAO_ALIHY' => self::NAME . '支付宝好友',
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
				\Log::channel('sifang_pay_callback')->info('秒支付回调错误提示：返回'.json_encode($callback_data));
				return false;
			}
            $key = $this->sdk_config['mch_key'] ?? '';
			$selfSign = strtoupper(NormalSignature::encrypt($callback_data,'sign','&key='.$key));
			if ($selfSign != $sign) {
				\Log::channel('sifang_pay_callback')->info('秒支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
				return false;
			}
			$money = $callback_data['amount'];
			$status = $callback_data['returncode'];
			$order = PaymentOrder::where('order_no', $callback_data['orderid'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
			if ($order && $order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['transaction_id'];
                $order->success_time   = date('Y-m-d H:i:s',strtotime($callback_data['datetime']));
				$order->callback_data = json_encode($original, true);
				if ($order->thirdAddCoins()) {
					\Log::channel('sifang_pay_callback')->info('秒支付回调成功提示：用户金币充值成功');
					return true;
				}else{
					\Log::channel('sifang_pay_callback')->info('秒支付回调错误提示：用户金币充值失败');
					return false;
				}
			}
			return false;
		}catch (\Exception $exception){
			\Log::channel('sifang_pay_callback')->info('秒支付回调错误提示：系统异常'.$exception->getMessage());
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
            case 'MIAO_ALISCAN':
                $provider_key  = '903';
                break;
            case 'MIAO_ALIHY':
                $provider_key  = '925';
                break;
            default:
                $provider_key = '903';
        }
        $key = $this->sdk_config['mch_key'] ?? '';
		//整理订单数据
		$data         = [
            'pay_amount'         => $order->amount,//订单金额
            'pay_bankcode'       => $provider_key,
            'pay_memberid'       => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'pay_callbackurl'    => $this->sdk_config['callback'] ?? '',
            'pay_notifyurl'      => $this->sdk_config['callback'] ?? '',
            'pay_orderid'        => $order->order_no,
            'pay_applydate'      => date('Y-m-d H:i:s'),
		];
		$data['pay_md5sign'] = strtoupper(NormalSignature::encrypt($data,'sign','&key='.$key)); //生成签名
        $data['pay_productname'] = 'miao_pay';
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
            $url = $this->sdk_config['gateway']; //拼装请求地址
            //form方式提交，由客户端发送请求
            return $this->show('FromSubmit', ['order'=>$data, 'action'=>$url,'title' => '秒支付','amount' => $order['amount']]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('秒支付请求异常:'.$exception->getMessage());
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
		echo 'OK';
		die;
	}

}