<?php
/*
 |--------------------------------------------------------------------------
 |
 |--------------------------------------------------------------------------
 | Notes:
 | Class PayAbstract
 | User: Administrator
 | Date: 2019/7/11
 | Time: 18:10
 |
 |  * @return
 |  |
 |
 */

namespace Modules\Payment\Packages\ThirdPay\Abstracts;


use Illuminate\Support\Facades\View;
use Models\AdminPlatform\PaymentConfig;
use Models\AdminPlatform\PaymentOrder;
use Modules\Payment\Packages\ThirdPay\Interfaces\CallBack;
use Modules\Payment\Packages\ThirdPay\Interfaces\Config;
use Modules\Payment\Packages\ThirdPay\Interfaces\PayTypes;
use Modules\Payment\Packages\ThirdPay\Interfaces\Send;

abstract class PayAbstract implements Config, PayTypes, Send, CallBack
{
    const APIS   = [];
    const CONFIG = [];
    const NAME   = '';

    const METHOD_GET  = 'GET';
    const METHOD_POST = 'POST';

    private $_connectTimeout = 7;
    private $_timeout        = 7;

    protected $sdk_config;

    /**
     * Http post请求
     *
     * @param string $url  http url address
     * @param array  $data post params name => value
     *
     * @return mixed
     */
    public function post($url, $data = array())
    {
        $queryString = $this->buildHttpQueryString($data, self::METHOD_POST);
        $response    = $this->makeHttpRequest($url, self::METHOD_POST, $queryString);
        return $response;
    }

    /**
     * http get 请求
     *
     * @param string $url
     * http url address
     * @param array  $data
     * get params name => value
     *
     * @return mixed
     */
    public function get($url, $data = array())
    {
        if (!empty($data)) {
            $url .= "?" . $this->buildHttpQueryString($data, self::METHOD_GET);
        }
        $response = $this->makeHttpRequest($url, self::METHOD_GET);
        return $response;
    }

    /**
     * 构造并发送一个http请求
     *
     * @param  $url
     * @param  $method
     * @param  $postFields
     *
     * @return array
     */
    public function makeHttpRequest($url, $method, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (self::METHOD_POST == $method) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($postFields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result       = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array('responseCode' => $responseCode, 'res' => $result);
    }

    /**
     * 构造http请求的查询字符串
     *
     * @param array $params
     * @param type  $method
     *
     * @return string
     */
    public function buildHttpQueryString(array $params, $method = self::METHOD_GET)
    {
        if (empty($params)) {
            return '';
        }
        if (self::METHOD_GET == $method) {
            $keys   = array_keys($params);
            $values = $this->urlEncode(array_values($params));
            $params = array_combine($keys, $values);
        }

        $fields = array();

        foreach ($params as $key => $value) {
            $fields[] = $key . '=' . $value;
        }
        return implode('&', $fields);
    }

    /**
     * url encode 函数
     *
     * @param type $item 数组或者字符串类型
     *
     * @return type
     */
    public function urlEncode($item)
    {
        if (is_array($item)) {
            return array_map(array(&$this, 'urlEncode'), $item);
        }
        return rawurlencode($item);
    }

    /**
     * url decode 函数
     *
     * @param type $item 数组或者字符串类型
     *
     * @return type
     */
    public function urlDecode($item)
    {
        if (is_array($item)) {
            return array_map(array(&$this, 'urlDecode'), $item);
        }
        return rawurldecode($item);
    }

    public function success()
    {
        echo 'success';
        die;
    }

    public function fail()
    {
        echo 'fail';
        die;
    }

    public function show($filename, $data)
    {
        return View::file(dirname(__DIR__) . '/views/' . $filename . '.blade.php', $data);
    }

    public function setConfig($config)
    {
        $this->sdk_config = $config;
    }


}
