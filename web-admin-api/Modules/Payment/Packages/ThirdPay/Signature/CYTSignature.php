<?php
namespace Modules\Payment\Packages\ThirdPay\Signature;
class CYTSignature
{
	private $public_key;
	private $private_key;
	private $md5_key;

	public function __construct($key = null)
    {
        $data['private_key'] = 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBANpEH4CQO+2nEkCO+B67YVl1/BDpVMq+05Bt4arRysrhr/FVgcBByYbzhNpz1tsFJR5lYFYfXdnOZ5wL0mK1a05lWeDolR39wlikzoIb+Ai9EsQOpHxzvNsx/sHDgfs2wtr7r4uJcUhNE9TctF4Xz4ET6lke+E7nrdr2gvd1sm9HAgMBAAECgYBYTtKeDf0pKHzygMWzjWWUL++1men0A6QOXd69YZcWYZxxXIKcGiHix1j7l32Y7Kp5c1O4VIWAr4ls8b2DsVoBxMKGbGNGK1UriiMKqrVnInMLEZheFrCqW5fNvYm/rox4OZYUHMOLgrctbJSD9HobKhUoVY7bCF1PedYz4KRRjQJBAPw+doUs0upCKEpJ39nbUyfjPzYHBEW9NI9iOHL8xpun4sJwenY3U6ArK46XSw4VPqaJjYO/dQOsSad+sN6j5iUCQQDdhCN/0cGTyh3LUrw45CYJo6aS2IfOKJOs9vCnzJhjtH3eYfxmqTqMjJetjRkNj1aICpbMNpJdxK1AuxYDh9X7AkEA23KcOhBdDmCwHLFYhnhBSBp0C9Te6q1I5NVWtvMi9piAtxiT8fUAVAA6zLrjGUVyVACnlU8jxiZFjeqyhX+h4QJAE3gJprJ';
        $data['public_key'] = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQChHfvDCS4nPAz9KyXOqDvoOPWRtjLvKLMcY7JGb7beEQKt8DpRdS0EoEDQFV9YsJz9SS2ptPdUduuqhHu0KzQlZ5HLT4onZr85390dkB/mEy8+1CB2hYfMb1cWnZLk/KzEg0W1mZGMqzOZzJrZWrS/rLTdfOKArerSB46LgWu3HQIDAQAB';

        $this->private_key = "-----BEGIN RSA PRIVATE KEY-----\r\n";
		foreach (str_split($data['private_key'],64) as $str){
			$this->private_key = $this->private_key . $str . "\r\n";
		}
		$this->private_key = $this->private_key . "-----END RSA PRIVATE KEY-----";
		
		$this->public_key = "-----BEGIN PUBLIC KEY-----\r\n";
		foreach (str_split($data['public_key'],64) as $str){
			$this->public_key = $this->public_key . $str . "\r\n";
		}
		$this->public_key = $this->public_key . "-----END PUBLIC KEY-----";

        $this->md5_key = '48ee55a41e07cd9fa4851f0d53adf301';
	}

	//获取签名
    public function sign($str) {
        return md5($str.$this->md5_key);
    }

    /**
     * [get_sign 拼接签名字符串]
     * @param  [type] $arr 数组
     * @return [type]      [description]
     */
    public function get_sign($arr) {
        $signmd5="";
        foreach($arr as $x=>$x_value)
        {
            if($signmd5==""){
                $signmd5 =$signmd5.$x .'='. $x_value;
            }else{
                $signmd5 = $signmd5.'&'.$x .'='. $x_value;
            }
        }
        return $signmd5;
    }

    /**
     * [publicEncrypt 公钥加密]
     * @param  [type] $publicKey 公钥
     * @param  [type] $data      加密字符串
     * @return [type]            [description]
     */
    public function publicEncrypt($data) {

        $key = openssl_get_publickey($this->public_key);

        $original_arr = str_split($data,117);
        foreach($original_arr as $o) {
            $sub_enc = null;
            openssl_public_encrypt($o,$sub_enc,$key);
            $original_enc_arr[] = $sub_enc;
        }

        openssl_free_key($key);
        $original_enc_str = base64_encode(implode('',$original_enc_arr));
        return $original_enc_str;
    }

    /**
     * [decode 私钥解密]
     * @param  [type] $data       [待解密字符串]
     * @param  [type] $privateKey [私钥]
     * @return [type]             [description]
     */
    public function privateDecrypt($data){
        //读取秘钥
        $pr_key = openssl_pkey_get_private($this->private_key);
        if ($pr_key == false){
            echo "打开密钥出错";
            die;
        }
        $data = base64_decode($data);
        $crypto = '';
        //分段解密
        foreach (str_split($data, 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $pr_key);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

    /**
     *[payVerify 支付验签]
     * @param  [type] $result [返回的参数]
     * @return [type] $md5    [MD5]
     */

    public function payVerify($result){

        $signStr = $result['sign'];
        $sign_array = array();
        foreach ($result as $k => $v) {
            if ($k !== 'sign'){
                $sign_array[$k] = $v;
            }
        }
        $sign  = md5($this->get_sign($sign_array).$this->md5_key);
        if($signStr != $sign){
            return false;
        }
        return true;
    }



}
