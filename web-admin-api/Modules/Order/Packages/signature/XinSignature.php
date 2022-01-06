<?php


namespace Modules\Order\Packages\Signature;


class XinSignature
{
    /**
     * MD5签名(需要拼接商户的秘钥)
     *
     * @param   string  $params         需要签名的数据
     * @param   string  $deskey         签名时拼接商户秘钥的键和值的拼接
     * return   string                  签名后得到的字串
     *
     */
    //PHP 7 以上用这个加密
    public static function encrypt($params, $deskey)
    {
        $str = self::pkcs5Pad($params);
        $data = openssl_encrypt($str, 'des-cbc', $deskey, 1, $deskey);
        $data = substr($data, 0, strlen($data) - strlen($deskey));
        $data = base64_encode($data);
        return $data;
    }

    public static function pkcs5Pad($text)
    {
        $len = strlen($text);
        $mod = $len % 8;
        $pad = 8 - $mod;
        return $text.str_repeat(chr($pad),$pad);

    }

}
