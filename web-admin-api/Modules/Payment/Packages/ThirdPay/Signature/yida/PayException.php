<?php
namespace Modules\Payment\Packages\ThirdPay\Signature\yida;
 /**
 * 
 * API异常类
 *
 */
class PayException extends \Exception
{
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
