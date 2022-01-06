<?php
namespace Modules\Payment\Packages\ThirdPay\Signature\yida;


/**
 * 
 * 接口调用结果类
 *
 */
class PayResults extends PayDataBase
{
	/**
	 * 
	 * 检测签名
	 */
	public function CheckSign($public_key)
	{
		//fix异常
		if(!$this->IsSignSet()){
			throw new PayException("签名错误！");
		}
		//signMsg,signType不参与验签
		$signMsg = $this->values['signMsg'];
		unset($this->values['signMsg']);
		$signType = $this->values['signType'];
		unset($this->values['signType']);
		//签名步骤一：按字典序排序参数
		ksort($this->values);
		//签名步骤二：组装字符串
		$signStr = $this->ToUrlParams();
		//签名步骤三：用密钥加密
		$rsa = new Handle_RSA();
		//用平台公钥验签名
		$result = $rsa->verity($signStr,$signMsg,$public_key);
		//回填参数
		$this->values['signMsg'] = $signMsg;
		$this->values['signType'] = $signType;
		//echo $result;
		//exit();
		if($result=='1'){
			return true;
		}
		throw new PayException("签名错误！");
	}
	
	/**
	 * 
	 * 使用数组初始化
	 * @param array $array
	 */
	public function FromArray($array)
	{
		$this->values = $array;
	}
	
	/**
	 * 
	 * 使用数组初始化对象
	 * @param array $array
	 * @param 是否检测签名 $noCheckSign
	 */
	public static function InitFromArray($array, $noCheckSign = false, $public_key)
	{
		$obj = new self();
		$obj->FromArray($array);
		if($noCheckSign == false){
			$obj->CheckSign($public_key);
		}
        return $obj;
	}
	
	/**
	 * 
	 * 设置参数
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}
	
    /**
     * 将json转为array
     * @param string $jsonStr
     * @param 是否检测签名 $noCheckSign
     * @throws PayException
     */
	public static function Init($jsonStr, $noCheckSign = false, $public_key)
	{	
		$obj = new self();
		$obj->FromJson($jsonStr);
		if(array_key_exists("errCode", $obj->values) && $obj->values['errCode'] != '0000'){
			 return $obj->GetValues();
		}
		if($noCheckSign == false){
			$obj->CheckSign($public_key);
		}
        return $obj->GetValues();
	}
}

