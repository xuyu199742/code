<?php


namespace Modules\Payment\Packages\ThirdPay\Signature;


class NormalSignature
{
    /**
     * 对请求进行加密
     *
     * @param array $data
     *                需要签名的数组
     * @param string $without
     *                要排除的项
     * @param string $append
     *                附加一起加密的数据
     *
     * @return String
     */
    public static function encrypt(array $data, string $without = 'sign',string $append = '')
    {
        ksort($data);
        $params = '';
        foreach ($data as $key => $value) {
            if ($value === '' || $value == null || $key == $without) {
                continue;
            }
            $params .= $key . '=' . $value . '&';
        }
        $params = rtrim($params, '&');
        return md5($params . $append);
    }


    /**
     * MD5签名(需要拼接商户的秘钥)
     *
     * @param   array   $data           需要签名的数据
     * @param   string  $secret_key     签名时拼接商户秘钥的键和值的拼接
     * @param   string  $is_strtoupper  MD5加密后是否进行大小写转换，默认转换
     *
     * return   string                  签名后得到的字串
     *
     */
    public static function signature(array $data, $secret_key = '' ,$is_strtoupper = true)
    {
        ksort($data);//对需要签名的数据进行排序
        $md5str = '';
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key .'=' . $val . '&';
        }
        //去除末尾拼接字符
        $md5str = rtrim($md5str, '&');
        //进行MD5加密
        $md5str = md5($md5str . $secret_key);
        //大写转换
        if ($is_strtoupper !== false){
            $md5str = strtoupper($md5str);
        }
        return $md5str;
    }

}
