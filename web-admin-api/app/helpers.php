<?php
/**
 * 计算两日期相差的天数
 * $strat_time      开始时间 ：yyyy-mm-dd
 * $end_time        结束时间 ：yyyy-mm-dd
 */
function gapDays($start_time, $end_time, $s = 86400)
{
    return ($end_time - $start_time) / $s;
}

/**
 * 日期或时间切分
 *
 * array 返回数据
 *
 */
function getDateRange($startdate, $enddate, $dayNum = 30, $s = 86400)
{
    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);
    // 计算日期段内有多少天
    $days = ($etimestamp - $stimestamp) / $s;
    if ($days > $dayNum) {
        $days = $dayNum;
    }
    $date = [];
    for ($i = 0; $i <= $days; $i++) {
        $date[$i] = date('Y-m-d', $etimestamp - ($s * $i));
    }
    return $date;
}

function system_configs($path)
{
    $paths = explode('.', $path);
    if (count($paths) == 2) {
        list($group, $key) = $paths;
    } else {
        list($group, $key) = [$paths[0], null];
    }
    /*$data = \Illuminate\Support\Facades\Cache::get($group);

    if ($data) {
        if ($key) {
            if (isset($data[$key])) {
                return $data[$key];
            }
        } else {
            return $data;
        }
    }*/
    $data = \Models\AdminPlatform\SystemSetting::where('group', $group)->pluck('value', 'key')->toArray();
    if ($data) {
        //\Illuminate\Support\Facades\Cache::set($group, $data);
        if ($key) {
            if (isset($data[$key])) {
                return $data[$key];
            }
        } else {
            return $data;
        }
    }
    return config($path);
}

/**
 * 获取二维码下载地址
 *
 * @param string $type 来源：app、h5，默认app
 */
function getQrcodeUrl($type = 'app')
{
    if ($type == 'app') {
        return system_configs('prots.app_download_url');
    } elseif ($type == 'h5') {
        return system_configs('prots.h5_download_url');
    }
}

//获取控制端的地址
function getControlUrl()
{
    return config('prots.control_site_url');
}

//金币配置获取
function getGoldBase()
{
    return config('coin.db_coin_base_ratio');
}

//获取充值比例
function getRechargeRatio()
{
    return config('coin.recharge_ratio');
}

//获取比例
function getWithdrawalRatio()
{
    return config('coin.withdrawal_ratio');
}

function getMinWithdrawal()
{
    return system_configs('coin.min_withdrawal') * realRatio();
}

//活动通知
function activityInform()
{
    try{
        $client = new \GuzzleHttp\Client(['base_uri' => getControlUrl()]);
        $client->request('GET', '/api/notify/system/activity', ['timeout' => 3]);
        return true;
    }catch (\Exception $exception){
        \Illuminate\Support\Facades\Log::error('活动通知服务器失败');
        return false;
    }
}
//签到通知
function signinInform()
{
    try{
        $client = new \GuzzleHttp\Client(['base_uri' => getControlUrl()]);
        $client->request('GET', '/api/notify/system/signin', ['timeout' => 3]);
        return true;
    }catch (\Exception $exception){
        \Illuminate\Support\Facades\Log::error('签到通知服务器失败');
        return false;
    }
}

//后台系统消息配送通知
function messageInform()
{
    try{
        $client = new \GuzzleHttp\Client(['base_uri' => getControlUrl()]);
        $client->request('GET', '/api/notify/system/message', ['timeout' => 3]);
        return true;
    }catch (\Exception $exception){
        \Illuminate\Support\Facades\Log::error('后台系统消息配送通知服务器失败');
        return false;
    }
}

//邮件通知
function eamilInform($userid)
{
    try{
        $client = new \GuzzleHttp\Client();
        $client->request('GET',   getControlUrl().'/api/notify/user/mail?userid=' . $userid, ['timeout' => 3]);
        if(env('WEB_APP_API', null)) {
            $client->request('POST', config('prots.web_app_api').'/account/emailNotice', [
                'form_params' => [
                    'user_id' => $userid,
                    'type' => 'email',
                ]
            ]);
        }
        return true;
    }catch (\Exception $exception){
        \Illuminate\Support\Facades\Log::error('邮件通知失败');
        return false;
    }
}

/**
 * 赠送金币发送通知
 *
 * @param int   $userid     用户id
 * @param int   $curscore   变化后的金币
 * @param int   $addscore   增量金币
 * @param int   $reason     1 赠送，2充值,默认2充值
 *
 * @return bool
 *
 */
function giveInform($userid, $curscore, $addscore, $reason = 2)
{
    if ($addscore == 0){
        return true;
    }
    try{
        $client = new \GuzzleHttp\Client();
        $uri = getControlUrl().'/api/notify/user/score?userid=' . $userid . '&curscore=' . $curscore . '&addscore=' . $addscore . '&reason='.$reason;
        $client->request('GET',$uri, ['timeout' => 3]);

        if (env('WEB_APP_API',null)){
            $client->request('POST',config('prots.web_app_api').'/account/emailNotice', [
                'form_params' => [
                    'type'          => 'score',
                    'user_id'       => $userid,
                    'score'         => realCoins($addscore),
                    'description'   => '增加金币',
                ]
            ]);
        }
        return true;
    }catch (\Exception $exception){
        \Illuminate\Support\Facades\Log::error('刷新金币通知服务器失败：'.$exception->getMessage());
        return false;
    }
}

/**
 * 游戏端成功结果返回格式
 *
 * @param string $message
 * @param array  $data
 *
 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
 */

function ResponeSuccess($message = '', $data = [], $transformer = '')
{
    if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource) {
        return $data->additional(['message' => $message, 'status' => true]);
    }
    /*if ($transformer) {
        if($data instanceof \Illuminate\Contracts\Pagination\Paginator){
            if ($data->isEmpty()) {
                $class = get_class($data);
            } else {
                $class = get_class($data->first());
            }
            $transformer=app('Dingo\Api\Transformer\Factory')->register($class, $transformer);
            return new Dingo\Api\Http\Response(['data'=>$data->items(),'message' => $message, 'status' => true], 200, [], $transformer);
        }else{
            $class = get_class($data);
        }
        $class = get_class($data);
        $transformer=app('Dingo\Api\Transformer\Factory')->register($class, $transformer);
        $transformer=app('Dingo\Api\Transformer\Factory')->register($class, $transformer);
        return new Dingo\Api\Http\Response($data, 200, [], $transformer);
    }*/
    return Response::json(['data' => $data, 'message' => $message, 'status' => true], 200);
}

function ResponeSuccessAppend($message = '', $data = [], $append = [])
{
    if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource) {
        if ($append) {
            return $data->additional(['message' => $message, 'status' => true, 'append' => $append]);
        }
        return $data->additional(['message' => $message, 'status' => true]);
    }
    return Response::json(['data' => $data, 'message' => $message, 'status' => true], 200);
}

/**
 * 游戏端失败结果返回格式
 *
 * @param string $message
 * @param array  $data
 * @param int    $code
 *
 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
 */
function ResponeFails($message = '', $data = [], $code = 200)
{
    if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource) {
        return $data->additional(['message' => $message, 'status' => true]);
    }

    return Response::json(['data' => $data, 'message' => $message, 'status' => false], $code);
}

/**
 * 金币转货币
 *
 * @param $coin
 * @param $isRealCoins true 当isRealCoins为true，传进来的金币会做一次转换，操作时如果游戏端传的值跟数据库中一致才可以用
 *
 * @return string|null
 */
function coinsToMoney($coin, $isRealCoins = false)
{
    //金币/比例/
    if ($isRealCoins) {
        $coin = bcdiv($coin, realRatio(), 4);
        return bcdiv($coin, getWithdrawalRatio(), 4);
    } else {
        return bcdiv($coin, getWithdrawalRatio(), 4);
    }
}

/**
 * 货币转金币 充值
 *
 * @param $money
 *
 * @return float|int
 */
function moneyToCoins($money)
{
    return $money * getRechargeRatio() * realRatio();
}

/**
 * 换算的除数，当充值比例大于10000，恢复成跟数据库一致的金币比例
 *
 * @return \Illuminate\Config\Repository|int|mixed
 */
function realRatio()
{
    $base           = getGoldBase();
    $recharge_ratio = getRechargeRatio();
    if ($base > $recharge_ratio) {
        return $base;
    } else {
        return 1;
    }
}

/*  金币转货币 */
function realCoins($coins)
{
    return bcdiv($coins, realRatio(), 2);
}

//状态码返回
function getMessage($code)
{
    switch ($code){
        case 404:
            $res = '错误404';
            break;
        case 500:
            $res = '服务器异常500';
            break;
        default :
            $res = '请求超时';
    }
    return $res;
}

function getIp()
{
    if (isset($_SERVER["HTTP_CLIENT_IP"]) && strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } else {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            if (isset($_SERVER["REMOTE_ADDR"]) && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown")) {
                $ip = $_SERVER["REMOTE_ADDR"];
            } else {
                if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],
                        "unknown")
                ) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $ip = "unknown";
                }
            }
        }
    }
    return ($ip);
}
/**
 * 按照单位转化时间
 * @param $timeData
 * @param $unit
 * @return array|bool
 */
function timeTransform($timeData,$unit = 'day'){
    if(!count($timeData)){
        return false;
    }
    //判断是否按月统计
    if($unit == 'month'){
        $timeData = getInitTime($timeData[0],'month');
    }
    //单日期该日起始
    if($timeData[1] == ''|| $timeData[0] == $timeData[1]){
        $timeData = getInitTime($timeData[0],'day');
    }else{
        if($unit !== 'month') {
            $startTime = date('Y-m-d 00:00:00', strtotime($timeData[0]));
            $endTime = date('Y-m-d 23:59:59', strtotime($timeData[1]));
            $timeData = [$startTime, $endTime];
        }
    }
    return $timeData;
}

/**
 * 获取该时间的天或月的起始时间
 * @param string $datetime (day|mouth)
 * @param string $unit
 * @return array
 */
function getInitTime($datetime,$unit = 'day'){
    switch ($unit){
        case 'day':
            $startTime = date('Y-m-d 00:00:00',strtotime($datetime));
            $endTime = date('Y-m-d 23:59:59',strtotime($datetime));
            break;
        case 'month':
            $startTime = date('Y-m-01 00:00:00',strtotime($datetime));
            $endTime = date('Y-m-t 23:59:59',strtotime($datetime));
            break;
    }
    return [$startTime,$endTime];
}


function coin_walk(&$value)
{
    if(is_numeric($value)){
        $value = realCoins($value);
    }

}

function decimal_walk(&$value)
{
    if(is_numeric($value)){
        $value = bcadd(0,$value,2);
    }
}
function dumpSql(){
    \DB::listen(function($query) {
        $bindings = $query->bindings;
        $sql = $query->sql;
        foreach ($bindings as $replace){
            $value = is_numeric($replace) ? $replace : "'".$replace."'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        dump($sql);
    });
}
function validate_data($value,$default=0){
    if(!empty($value)){
        return $value;
    }
    return $default;
}

//推送数据存储
function setPushStorage($type){
    $storage = getPushStorage();
    foreach($storage as $k => $item){
        if($k == $type){
            $storage[$k] += 1;
        }
    }
    Cache::set('pushData',$storage);
    return true;
}

//获取推送数据
function getPushStorage(){
    $default = [
        'officialPay' => 0,
        'thirdPay' => 0,
        'withdraw' => 0,
        'email' => 0,
    ];
    $storage = Cache::get('pushData');
    if(empty($storage)){
        $storage = $default;
    }
    return $storage;
}

//已读推送数据
function readPushStorage($type){
    $storage = getPushStorage();
    if(isset($storage[$type])){
        $storage[$type] = 0;
        Cache::set('pushData',$storage);
    }
    return true;
}

function notify_user_vip($userid, $vipexp, $viplevel)
{
	try{
		$client = new \GuzzleHttp\Client(['base_uri' => getControlUrl()]);
		$uri = '/api/notify/user/vip?userid=' . $userid . '&vipexp=' . $vipexp . '&viplevel=' . $viplevel;
		$client->request('GET',$uri, ['timeout' => 3]);
		return true;
	}catch (\Exception $exception){
		\Illuminate\Support\Facades\Log::error('通知等级失败');
		return false;
	}
}

function cdn($path){
	return env('CDN')?env('CDN').$path:asset('storage/'.$path);
}

/**
 * 秒装换成（时:分:秒）
 * $time   int     $time  秒
 */
function secondTransform($time)
{
    $h = floor($time/3600);
    $i = floor(($time%3600)/60);
    $s = floor(($time%3600)%60);
    if ($h < 10){ $h = '0'.$h; }
    if ($i < 10){ $i = '0'.$i; }
    if ($s < 10){ $s = '0'.$s; }
    return $h.':'.$i.':'.$s;
}

/**
 * （时:分:秒）转换成秒
 *
 */
function timesTransform($time)
{
    $arr = explode(':',$time);
    return intval($arr[0]) * 3600 + intval($arr[1]) * 60 + intval($arr[2]);
}



/**
 * 统计二维数组某个字段的和
 */
function twin_sum($arr,$field)
{
    return array_sum(array_column($arr, $field));
}
//返回当前的毫秒时间戳
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (string)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

/**
 * 年月日、时分秒 + 3位毫秒数
 * @param string $format
 * @param null $utimestamp
 * @return false|string
 */
function tsTime($format = 'u', $utimestamp = null)
{
    if (is_null($utimestamp)) {
        $utimestamp = microtime(true);
    }

    $timestamp    = floor($utimestamp);
    $milliseconds = round(($utimestamp - $timestamp) * 1000);

    return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}


/**
 * [array_group_by ph]
 * @param  [type] $arr [二维数组]
 * @param  [type] $key [键名]
 * @return [type]      [新的二维数组]
 */
function array_group_by($arr, $key){
    $grouped = array();
    foreach ($arr as $value) {
        $grouped[$value[$key]][] = $value;
    }
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $parms = array_merge($value, array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('array_group_by', $parms);
        }
    }
    return $grouped;
}

/**
 * 计算当前距离1970年有多少天
 *
 */
function countDays()
{
    return 25566 + intval(ceil((time())/86400));
}

/**
 * OSS文件上传
 * @param $file
 * @return array
 */
function ossUploadFile($filePath,$fileName){
    if(!env('ALIYUN_OSS')){
        return [true,''];
    }
    $accessKeyId = env('ALIYUN_ACCESS_ID');
    $accessKeySecret = env('ALIYUN_ACCESS_KEY');
    $endpoint = env('ALIYUN_ENDPOINT');
    $bucket = env('ALIYUN_BUCKET');
    try{
        $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $result = $ossClient->uploadFile($bucket, $fileName, $filePath);
        $url = $result['info']['url'] ?? '';
        if(!$url){
            return [false,''];
        }
        return [true,$url];
    } catch(\Exception $e) {
        return [false,':'.$e->getMessage()];
    }
}

/**
 * 请求频率限制
 * @UserID
 * @return bool
 */
function reqAstrict($UserID,$hz){
    if(!$UserID || !$hz){
        return false;
    }
    $paySend = Cache::get('paySend');
    $ip = getIp();
    $time = time();
    $userInfo = $paySend[$UserID] ?? '';
    if($userInfo){
        if($userInfo['ip'] == $ip && $userInfo['time'] + $hz > $time){
            return false;
        }
    }
    return true;
}

function paySendSet($UserID){
    $ip = getIp();
    $time = time();
    $data[$UserID] = [
        'ip' => $ip,
        'time' => $time
    ];
    Cache::set('paySend',$data);
}




