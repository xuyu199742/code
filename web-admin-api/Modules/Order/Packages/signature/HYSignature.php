<?php
namespace Modules\Order\Packages\Signature;

class HYSignature
{
    private $key = '';
    private $iv = '';

    function __construct ($key)
    {
        if (empty($key)) {
            echo 'key is not valid';
            exit();
        }
        $this->key = $key;
        $this->iv = substr($key,0,8);
    }

    public function encrypt ($value)
    {
        $value = $this->PaddingPKCS7($value);
        $key = $this->key;
        $iv  = $this->iv;
        $cipher = "DES-EDE3-CBC";
        if (in_array(strtolower($cipher),array_map('strtolower',openssl_get_cipher_methods()))) {
            $result = openssl_encrypt($value, $cipher, $key, OPENSSL_SSLV23_PADDING, $iv);
        }
        return $result;
    }

    public function decrypt ($value)
    {
        $key       = $this->key;
        $iv        = $this->iv;
        $decrypted = openssl_decrypt($value, 'DES-EDE3-CBC', $key, OPENSSL_SSLV23_PADDING, $iv);
        $ret = $this->UnPaddingPKCS7($decrypted);
        return $ret;
    }

    private function PaddingPKCS7 ($data)
    {
        $block_size = 8;
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

    private function UnPaddingPKCS7($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, - 1 * $pad);
    }

    public function create_sign($array)
    {
        ksort($array); #排列数组 将数组已a-z排序
        $result = '';
        foreach($array as $key=>$v){
            if ($key !== 'notifyurl' && $key !== 'sign'){
                $v = trim($v);
                if($v != '0'){
                    $result  .= $key  . '=' . $v . '&';
                }
            }
        }
        return $result;
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

    public function replace_unicode_escape_sequence($match)
    {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }

}
