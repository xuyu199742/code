<?php
namespace Modules\Payment\Packages\ThirdPay\Signature\yida;

class PayUtil
{

	/**
	* 以post方式提交json到对应的接口url
	* 
	* @param string $jsonStr  需要post的数据
	* @param string $url  url
	* @param int $second   url执行超时时间，默认30s
	* @throws PayException
	*/
	public static function postCurl($postdata, $url, $second = 30) {
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_URL, $url);

		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			throw new PayException("curl出错，错误码:$error");
		}
	}
	
	/**
	 * 获取毫秒级别的时间戳
	 */
	public static function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
	
	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
	/**
	 * 获取客户端IP
	 */
	public static function getClientIP()
    {
		if (getenv("HTTP_CLIENT_IP")) {  
		    $ip = getenv("HTTP_CLIENT_IP");  
		}elseif(getenv("HTTP_X_FORWARDED_FOR")) {  
		    $ip = getenv("HTTP_X_FORWARDED_FOR");  
		}elseif(getenv("REMOTE_ADDR")) {  
		$ip = getenv("REMOTE_ADDR");  
		}else $ip = "Unknow";   
		return $ip; 
	}
}

