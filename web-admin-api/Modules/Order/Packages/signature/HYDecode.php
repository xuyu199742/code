<?php
namespace Modules\Order\Packages\Signature;

class HYDecode
{
    /** 请求参数 */
    protected $parameters;
    /** 解密参数 */
    protected $params;
    /** 密钥 */
    protected $key;

    /**
     *构告初始
     *@param array $post 参数数组值
     */
    public function __construct($post = null, $key = null)
    {
        //设置参数
        if ($post) {
            if (is_array($post)) {
                $this->setAllParams($post);
            }else{
                $this->params = $post;
            }
        }
        if($key) $this->key = $key;
    }

    /**
     *获取参数值
     *@param string $parameter 参数键
     *@return string 参数值
     */
    public function getParameter($parameter)
    {
        return isset($this->parameters[$parameter])?$this->parameters[$parameter]:'';
    }

    /**
     *设置参数值
     *@param string $parameter 参数键
     *@param string $parameterValue 参数值
     */
    public function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$parameter] = $parameterValue;
    }

    /**
     *设置参数值
     *@param string $parameter 参数键
     *@param string $parameterValue 参数值
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     *获取参数值
     *@return string 参数值
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 一次性设置参数
     *@param array $post 参数数组
     *@param array $filterField 过虑参数
     */
    public function setAllParams($post,$filterField=null)
    {
        if($filterField){
            foreach($filterField as $k=>$v){
                unset($post[$v]);
            }
        }
        //判断是否存在空值，空值不提交
        foreach($post as $k=>$v){
            if($v == ""){
                unset($post[$k]);
            }
        }
        $this->parameters = $post;
    }

    /**
     * 对数组排序
     * @param array $para 排序前的数组
     * return 排序后的数组
     */
    public function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param array $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    public function paraFilter($para)
    {
        $para_filter = array();
        foreach ($para as $k=>$v){
            if($k == "sign" || $v == "") continue;
            else $para_filter[$k] = $para[$k];
        }
        return $para_filter;
    }

    /**
     * 生成签名
     * @param array $data 签名数组
     * return string 签名后的字符串
     */
    public function dataSign($data)
    {
        $data = $this->paraFilter($data);
        $data = $this->argSort($data);
        $data_signstr = "";
        foreach ($data as $k => $v) {
            $data_signstr .= $k . '=' . $v . '&';
        }
        $data_signstr .= 'key='.$this->key;
        return strtoupper(md5($data_signstr));
    }

    /**
     * 验证签名
     * @param array $data 数组
     * @param array $sign 签名
     * return bool
     */
    public function verifySign($data,$sign)
    {
        $verify_sign = $this->dataSign($data);
        if($verify_sign != $sign) return false;
        return true;
    }

    /**
     * 请求参数加密
     * @return string 加密字符串
     */
    public function requestParamsEncode()
    {
        $des = new HYSignature($this->key);
        $this->setParameter('timestamp',time());
        $this->setParameter('sign',$this->dataSign($this->parameters));
        $params = json_encode($this->parameters);
        $this->params = base64_encode($des->encrypt($params));
        return $this->params;
    }

    /**
     * 请求参数解密
     * @param  bool   true为返回数组，false为返回对象
     * @return array/object 解密后的数组或对象
     */
    public function requestParamsDecode($retArr = true)
    {
        $params = base64_decode($this->params);
        $des = new HYSignature($this->key);
        $retArr = $retArr ? true : false;
        return json_decode($des->decrypt($params), $retArr);
    }

}
