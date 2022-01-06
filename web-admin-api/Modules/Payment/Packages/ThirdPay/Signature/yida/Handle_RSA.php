<?php
namespace Modules\Payment\Packages\ThirdPay\Signature\yida;

class Handle_RSA
{
    
    /**
     * ras签名
     * @param $data
     * @param $code
     */
    function get_sign($data, $private_key,$code = 'base64')
    {
        $ret = false;
		 $private_key= chunk_split($private_key, 64, "\n");
         $private_key = "-----BEGIN RSA PRIVATE KEY-----\n$private_key-----END RSA PRIVATE KEY-----\n";
        if (openssl_sign($data, $ret, $private_key,OPENSSL_ALGO_SHA1)){
            $ret = $this->_encode($ret, $code);
        }
        return $ret;
    }
    /**
     * 编码格式
     * @param $data
     * @param $code
     */
    function _encode($data, $code)
    {
        switch (strtolower($code)){
            case 'base64':
                $data = base64_encode(''.$data);
                break;
            case 'hex':
                $data = bin2hex($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    /* 
    验证签名： 
    data：原文 
    signature：签名 
    publicKeyPath：公钥 
    返回：签名结果，true为验签成功，false为验签失败 
    */  
    function verity($data, $signature, $publicKey)  
    {  
        $pubKey = $publicKey;  
		$pubKey= chunk_split($pubKey, 64, "\n");
         $pubKey =  "-----BEGIN PUBLIC KEY-----\n".$pubKey."-----END PUBLIC KEY-----\n";
		//$res = openssl_pkey_get_public($pubKey);
        $res = openssl_get_publickey($pubKey);  
        $result =openssl_verify($data, base64_decode($signature), $res,OPENSSL_ALGO_SHA1);  
        openssl_free_key($res);  
     //   echo "签名结果".$result;
		//exit();
        return $result;  
    }  
    
}