<?php

namespace Modules\Order\Packages\lib;

use GuzzleHttp\Client;
use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Modules\Order\Packages\Signature\NormalSignature;

class Base
{
    //定义三方接口请求返回成功标识
    protected $request_success = 1;
    //定义三方接口请求返回失败标识
    protected $request_error = '';
    //定义三方回调成功通知标识
    protected $callback_success = 'SUCCESS';
    //定义三方回调失败通知标识
    protected $callback_error = 'fail';
    //定义三方配置
    protected $config = [];
    //定义三方错误提示信息
    protected $message = [];

    //配置三方基本配置
    const CONFIG = [];
    //配置三方状态码
    const MESSAGE = [];

}
