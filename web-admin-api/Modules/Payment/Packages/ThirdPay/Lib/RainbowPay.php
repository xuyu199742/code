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

class RainbowPay extends PayAbstract
{

	const NAME = '彩虹支付';

	const CONFIG = [
		'gateway' => '支付网关',
		'mch_id'  => '商户APPID',
		'mch_key' => '商户APPKEY',
	];

	//支付通道配置
	const APIS = [
		self::ALIPAY_H5 => [
            'RAINBOW_ALIH5' => self::NAME . '支付宝H5',
		],
		self::WECHAT_H5 => [
			'RAINBOW_WECHATH5' => self::NAME . '微信H5',
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
				\Log::channel('sifang_pay_callback')->info('彩虹支付回调错误提示：返回'.json_encode($callback_data));
				return false;
			}
            $key = $this->sdk_config['mch_key'] ?? '';
			$selfSign = strtoupper(NormalSignature::encrypt($callback_data,'sign','&key='.$key));
			if ($selfSign != $sign) {
				\Log::channel('sifang_pay_callback')->info('彩虹支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
				return false;
			}
			$money = $callback_data['amount'];
			$status = $callback_data['returncode'];
            list($id,$user_id) = explode('o',$callback_data['orderid']);
			$order = PaymentOrder::where('id',$id)->where('user_id',$user_id)->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
			if ($order && $order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['transaction_id'];
                $order->success_time   = date('Y-m-d H:i:s');
				$order->callback_data = json_encode($original, true);
				if ($order->thirdAddCoins()) {
					\Log::channel('sifang_pay_callback')->info('彩虹支付回调成功提示：用户金币充值成功');
					return true;
				}else{
					\Log::channel('sifang_pay_callback')->info('彩虹支付回调错误提示：用户金币充值失败');
					return false;
				}
			}
			return false;
		}catch (\Exception $exception){
			\Log::channel('sifang_pay_callback')->info('彩虹支付回调错误提示：系统异常'.$exception->getMessage());
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
            case 'RAINBOW_ALIH5':
                $provider_key  = '903';
                break;
            case 'RAINBOW_WECHATH5':
                $provider_key  = '902';
                break;
            default:
                $provider_key = '903';
        }
        $key = $this->sdk_config['mch_key'] ?? '';
		//整理订单数据
		$data         = [
            'pay_amount'         => number_format(floatval($order->amount), 2, '.', ''),//订单金额
            'pay_bankcode'       => $provider_key,
            'pay_memberid'       => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'pay_callbackurl'    => $this->sdk_config['callback'] ?? '',
            'pay_notifyurl'      => $this->sdk_config['callback'] ?? '',
            'pay_orderid'        => $order->id.'o'.$order->user_id,  //因为这个四方限制订单号为20位以内
            'pay_applydate'      => date('Y-m-d H:i:s'),
		];
		$data['pay_md5sign'] = strtoupper(NormalSignature::encrypt($data,'sign','&key='.$key)); //生成签名
        $data['pay_productname'] = 'pay';
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
            return $this->show('FromSubmit', ['order'=>$data, 'action'=>$url,'title' => '彩虹支付','amount' => $order['amount']]);
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('彩虹支付请求异常:'.$exception->getMessage());
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