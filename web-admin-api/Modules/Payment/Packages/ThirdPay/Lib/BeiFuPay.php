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

class BeiFuPay extends PayAbstract
{

	const NAME = '贝富支付';

	const CONFIG = [
		'gateway' => '支付网关',
		'mch_id'  => '商户号',
		'mch_key' => 'md5key',
        'mch_syspwd' => 'syspwd',
        'mch_deskey' => 'deskey'
	];

	//支付通道配置
	const APIS = [
		self::ALIPAY_H5 => [
            'BEIFU_ALIH5' => self::NAME . '支付宝',
		],
        self::ALIPAY_QRCODE => [
            'BEIFU_ALISCAN' => self::NAME . '支付宝',
        ],
		self::WECHAT_H5 => [
			'BEIFU_WECHATH5' => self::NAME . '微信',
		],
        self::WECHAT_QRCODE => [
            'BEIFU_WECHATSCAN' => self::NAME . '微信',
        ],
        self::UNIONPAY_QRCODE => [
            'BEIFU_UNIONPAY' => self::NAME . '银联',
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
			$sign = $callback_data['p_md5'] ?? '';
			if(!$sign){
				\Log::channel('sifang_pay_callback')->info('贝富支付回调错误提示：返回'.json_encode($callback_data));
				return false;
			}
            $adminKey = $this->sdk_config['mch_syspwd'] ?? '';
            $md5Key = $this->sdk_config['mch_key'] ?? '';
            $param = $callback_data['p_name'].$callback_data['p_oid'].$callback_data['p_money'].$adminKey;
            $selfSign = md5($param.$md5Key);
			if ($selfSign != $sign) {
				\Log::channel('sifang_pay_callback')->info('贝富支付回调错误提示：签名有误,内部签名：' . $selfSign . '返回数据：' . json_encode($callback_data));
				return false;
			}
			$money = $callback_data['p_money'];
			$status = $callback_data['p_code'];
			$order = PaymentOrder::where('order_no', $callback_data['p_oid'])->where('payment_status', PaymentOrder::WAIT)->first(); //查询订单
			if ($order && $order->amount == $money && $status == self::SUCCESS) {
                $order->third_order_no = $callback_data['p_sid'];
                $order->success_time   = date('Y-m-d H:i:s');
				$order->callback_data = json_encode($original, true);
				if ($order->thirdAddCoins()) {
					\Log::channel('sifang_pay_callback')->info('贝富支付回调成功提示：用户金币充值成功');
					return true;
				}else{
					\Log::channel('sifang_pay_callback')->info('贝富支付回调错误提示：用户金币充值失败');
					return false;
				}
			}
			return false;
		}catch (\Exception $exception){
			\Log::channel('sifang_pay_callback')->info('贝富支付回调错误提示：系统异常'.$exception->getMessage());
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

	const SUCCESS = '1';//成功

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
            case 'BEIFU_ALISCAN':
                $provider_key  = 'WAY_TYPE_ALIPAY';
                break;
            case 'BEIFU_ALIH5':
                $provider_key  = 'WAY_TYPE_ALIPAY_PHONE';
                break;
            case 'BEIFU_WECHATSCAN':
                $provider_key  = 'WAY_TYPE_WEBCAT';
                break;
            case 'BEIFU_WECHATH5':
                $provider_key  = 'WAY_TYPE_WEBCAT_PHONE';
                break;
            case 'BEIFU_UNIONPAY':
                $provider_key  = 'WAY_TYPE_BANK_QR';
                break;
            default:
                $provider_key = 'WAY_TYPE_ALIPAY';
        }
        $adminKey = $this->sdk_config['mch_syspwd'] ?? '';
        $md5Key = $this->sdk_config['mch_key'] ?? '';
		//整理订单数据
		$data         = [
            'p_name'    => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'p_type'    => $provider_key,
            'p_oid'     => $order->order_no,
            'p_money'   => number_format(floatval($order->amount), 2, '.', ''),//订单金额
            'p_url'     => $this->sdk_config['callback'] ?? '',
            'p_surl'    => $this->sdk_config['callback'] ?? '',
            'p_remarks' => 'pay',
            'uname'     => $this->sdk_config['mch_id'] ?? '', //设置配置参数
            'p_syspwd'  => md5($adminKey . $md5Key)
		];
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
            $url = $this->sdk_config['gateway'].'/api/pay'; //拼装请求地址
            $desKey = $this->sdk_config['mch_deskey'] ?? '';
            $httpUrl = $this->url($url, $data, $desKey);
            header("Location:$httpUrl");
        }catch (\Exception $exception){
            \Log::channel('sifang_pay_send')->info('贝富支付请求异常:'.$exception->getMessage());
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

    public function url($payApiUrl, $requestarray, $desk)
    {
        $uname = $requestarray['uname'];
        unset($requestarray['uname']);
        $s = '';
        foreach ($requestarray as $requestarraykey => $requestarrayVal) {
            $s .= "$requestarraykey=$requestarrayVal!";
        }
        $s = urlencode(rtrim($s, '!'));
        $desStr = $this->encrypt($s, $desk);
        $url = $payApiUrl . '?params=' . $desStr . '&uname=' . $uname;
        return $url;
    }

    public function encrypt($params, $deskey)
    {
        $str = $this->pkcs5Pad($params);
        $data = openssl_encrypt($str, 'des-cbc', $deskey, 1, $deskey);
        $data = substr($data, 0, strlen($data) - strlen($deskey));
        $data = base64_encode($data);
        return $data;
    }

    public function pkcs5Pad($text)
    {
        $len = strlen($text);
        $mod = $len % 8;
        $pad = 8 - $mod;
        return $text.str_repeat(chr($pad),$pad);

    }

}