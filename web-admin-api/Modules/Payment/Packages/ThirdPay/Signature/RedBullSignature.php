<?php


namespace Modules\Payment\Packages\ThirdPay\Signature;


class RedBullSignature
{
    /**
     * 签名数据排序以及拼接
     *
     */
    public static function encrypt($data)
    {
        ksort($data);
        reset($data);
        $rsa_str = '';
        foreach ($data as $key => $val) {
            $rsa_str = $rsa_str . $key . '=' . $val . '&';
        }
        return rtrim($rsa_str,'&');
    }

    /**
     * RAS签名
     *
     * @param string    $param          要签名的数据
     * @param string    $private_key    配置的私钥
     *
     */
    public static function rsaSign($param, $private_key)
    {
        $search = [
            "-----BEGIN RSA PRIVATE KEY-----",
            "-----END RSA PRIVATE KEY-----",
            "\n",
            "\r",
            "\r\n"
        ];
        $private_key=str_replace($search,"",$private_key);
        $private_key=$search[0] . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . $search[1];
        $res=openssl_get_privatekey($private_key);
        if($res) {
            openssl_sign($param, $sign, $res, OPENSSL_ALGO_MD5);
            openssl_free_key($res);
        }else {
            exit("私钥格式有误");
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * RAS验签
     *
     * @param string    $param          要签名的数据
     * @param string    $private_key    配置的公钥
     * @param string    $sign           回调时返回的签名字串
     *
     */
    public static function rsaCheck($param, $public_key, $sign)
    {
        $search = [
            "-----BEGIN PUBLIC KEY-----",
            "-----END PUBLIC KEY-----",
            "\n",
            "\r",
            "\r\n"
        ];
        $public_key=str_replace($search,"",$public_key);
        $public_key=$search[0] . PHP_EOL . wordwrap($public_key, 64, "\n", true) . PHP_EOL . $search[1];
        $res=openssl_get_publickey($public_key);
        if($res) {
            $result = (bool)openssl_verify($param, base64_decode($sign), $res, OPENSSL_ALGO_MD5);
            openssl_free_key($res);
        }else{
            exit("公钥格式有误!");
        }
        return $result;
    }

}
