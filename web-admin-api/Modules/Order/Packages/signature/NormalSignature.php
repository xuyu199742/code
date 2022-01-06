<?php


namespace Modules\Order\Packages\Signature;


class NormalSignature
{
    /**
     * MD5签名(需要拼接商户的秘钥)
     *
     * @param   array   $data           需要签名的数据
     * @param   string  $secret_key     签名时拼接商户秘钥的键和值的拼接
     * return   string                  签名后得到的字串
     *
     */
    public static function encrypt(array $data, $pay_key = '')
    {
        ksort($data);//对需要签名的数据进行排序
        $md5str = '';
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key .'=' . $val . '&';
        }
        //去除末尾拼接字符
        $md5str = rtrim($md5str, '&');
        //进行MD5加密
        $md5str = md5($md5str . $pay_key);
        return $md5str;
    }

}
