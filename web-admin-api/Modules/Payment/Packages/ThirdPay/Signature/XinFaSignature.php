<?php
namespace Modules\Payment\Packages\ThirdPay\Signature;
class XinFaSignature
{
	private $pay_public_key;
	private $remit_public_key;
	private $private_key;

	public function __construct($key = null)
    {
        $data['private_key'] = 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAIq32sL7qQlvSuodol7uf68Bi4C/QhQZrcH9mURRi2JU4lalyK8WWqld4oyO1tQ4VhKNaDSj7LrcqAlkKvRks0jd9bn6reYSu9BpmbKrZxWA5LUrAx0hvw7BRSxO20daWb+9vLNavtOTkuIBdbCa+hidSW8nmbVKQAFUJG+OdAvrAgMBAAECgYBB6YJEy5dSKFOMUnBocyKwYSeMEVSwgFMTrhA5ahW0r2isizGEIDcL6tROvUBOrkYXoqE1Af7l+xrM+499eKvG6n5iVTD/01tT8gurHQWd/jghuCf+Mrxbw14fQ5g2J4uUzCAELzSYu3V2v+0Rjmz3xUODvr+va+J6D68+M6S+oQJBANytwjMLAdj8e05BwB1y7ca/OTgrtFvLLxdYwWbawEQm+2vz06N6xUOok3mT4M7ceBDqNGz2I6ZJ+U8Xe/8YZccCQQCg68m2kzsAu+Ko+h7gSeQnAc5JgklpoH4qau7BiEpRzWIp9KwpGlwdWtmE27dNl1hAYi4/XMkFLsXOlZnL59i9AkEAyp7IJrG+mosIdIwuZ1u1Mr01PnvyeC4RKPCXc2b7Dhh01WPlOL9rDHpmHkVHLuiDXZSNWlaN2Qsm3BYGBe1S1QJACmVxULROWYvJ5vTRzde4P7TkKOeb6pEN1Zu29RXjB0nnj6JZBQbI8LSP3P985ixi7TXqmvlZm1YcPW540y2VWQJAFXUaAcfmAGfUTsmSrT6n7T3YvgMibv7mgQmieiM4Cm+6JqUT1iyhYdBndw5z4X14ZkosgQ4o6srKCP5HhhLqRA==';
        $data['pay_public_key'] = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCGmPfpo2afqJRDSvNxklrQIs+TAVbVNj5lt9ojwD/pOa30PXswnMHIOXd9VLbzS9+Z4pq3jTZWHO9CCCUbEXESxqnLXfPOoX8DprLI6UPiQPQM1DWjDg1TpMmiai0Mv04QHQmrbEe/fT3FF6wZ49JN0vF1IaZtJ/V5pMcvILa80QIDAQAB';
        $data['remit_public_key'] = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCz/Sv8YqsylkkhXCZ8Dkqu2CiQj0yxJSTSSr+uxktXk0E7epEZcEz8idGuldFibsWzQf7RlQ68BfEH4wFTZp1rOVT/fbXwr21u/AX4DGMBu3bGuvcZorLpjSU9Z4FMBqIXrU22l1759hQG0/s7Ti/ud9mafGlENOW77C5NdrD1JwIDAQAB';

        $this->private_key = "-----BEGIN RSA PRIVATE KEY-----\r\n";
		foreach (str_split($data['private_key'],64) as $str){
			$this->private_key = $this->private_key . $str . "\r\n";
		}
		$this->private_key = $this->private_key . "-----END RSA PRIVATE KEY-----";
		
		$this->pay_public_key = "-----BEGIN PUBLIC KEY-----\r\n";
		foreach (str_split($data['pay_public_key'],64) as $str){
			$this->pay_public_key = $this->pay_public_key . $str . "\r\n";
		}
		$this->pay_public_key = $this->pay_public_key . "-----END PUBLIC KEY-----";
		
		
		
		$this->remit_public_key = "-----BEGIN PUBLIC KEY-----\r\n";
		foreach (str_split($data['remit_public_key'],64) as $str){
		    $this->remit_public_key = $this->remit_public_key . $str . "\r\n";
		}
		$this->remit_public_key = $this->remit_public_key . "-----END PUBLIC KEY-----";
	}

	public function encode_pay($data)
    {#加密
		$pu_key = openssl_pkey_get_public($this->pay_public_key);
		if ($pu_key == false){
			echo "打开密钥出错";
			die;
		}
		$encryptData = '';
		$crypto = '';
		foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $pu_key);
            $crypto = $crypto . $encryptData;
        }

		$crypto = base64_encode($crypto);
		return $crypto;

	}
	
	
	public function encode_remit($data)
    {#加密
	    $pu_key = openssl_pkey_get_public($this->remit_public_key);
	    if ($pu_key == false){
	        echo "打开密钥出错";
	        die;
	    }
	    $encryptData = '';
	    $crypto = '';
	    foreach (str_split($data, 117) as $chunk) {
	        openssl_public_encrypt($chunk, $encryptData, $pu_key);
	        $crypto = $crypto . $encryptData;
	    }
	    
	    $crypto = base64_encode($crypto);
	    return $crypto;
	    
	}



	public function decode($data)
    {
		$pr_key = openssl_get_privatekey($this->private_key);
		if ($pr_key == false){
			echo "打开密钥出错";
			die;
		}
		$data = base64_decode($data);
		$crypto = '';
        foreach (str_split($data, 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $pr_key);
            $crypto .= $decryptData;
        }
        return $crypto;
	}


    public function p($array)
    {
        $str = '<pre>' . print_r($array,true) . '</pre>';
        echo $str;
    }

    public function ps($array){
        $str =  print_r($array,true);
        return $str;
    }

    public function wx_post($url,$data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        return $tmpInfo;

    }

    public function json_encode_ex($value)
    {
        if (version_compare(PHP_VERSION,'5.4.0','<')){
            $str = json_encode($value);
            $str = preg_replace_callback("#\\\u([0-9a-f]{4})#i","replace_unicode_escape_sequence",$str);
            $str = stripslashes($str);
            return $str;
        }else{
            return json_encode($value,320);
        }
    }

    public function json_decode_ex($value)
    {
        return json_decode($value,true);
    }

    public function replace_unicode_escape_sequence($match)
    {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }

    public function log_write($log)
    {
        $file = date('Y-m-d') . '.log';
        $str = date('H:i:s') . " " . $log . "\r\n";
        file_put_contents($file,$str,FILE_APPEND);
    }

    public function create_sign($data,$key)
    {
        ksort($data);
        $sign = strtoupper(md5($this->json_encode_ex($data) . $key));
        return $sign;
    }

    public function json_to_array($json,$key){
        $array = $this->json_decode_ex($json);
        if ($array['stateCode'] == '00'){
            $sign_string = $array['sign'];
            ksort($array);
            $sign_array = array();
            foreach ($array as $k => $v) {
                if ($k !== 'sign'){
                    $sign_array[$k] = $v;
                }
            }

            $md5 =  strtoupper(md5($this->json_encode_ex($sign_array) . $key));
            if ($md5 == $sign_string){
                return $sign_array;
            }else{
                $result = array();
                $result['stateCode'] = '99';
                $result['msg'] = '返回签名验证失败';
                return $result;
            }



        }else{
            $result = array();
            $result['stateCode'] = $array['stateCode'];
            $result['msg'] = $array['msg'];
            return $result;
        }

    }


    function callback_to_array($json,$key)
    {
        $array = $this->json_decode_ex($json);
        $sign_string = $array['sign'];
        ksort($array);
        $sign_array = array();
        foreach ($array as $k => $v) {
            if ($k !== 'sign'){
                $sign_array[$k] = $v;
            }
        }

        $md5 =  strtoupper(md5($this->json_encode_ex($sign_array) . $key));
        if ($md5 == $sign_string){
            return $sign_array;
        }else{
            $result = array();
            $result['payStateCode'] = '99';
            $result['msg'] = '返回签名验证失败';
            return $result;
        }

    }


}
