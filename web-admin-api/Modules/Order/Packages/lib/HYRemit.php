<?php

namespace Modules\Order\Packages\lib;

use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;
use Modules\Order\Packages\Signature\HYDecode;
use Modules\Order\Packages\Signature\HYSignature;

class HYRemit extends Base
{
    //定义三方接口请求返回成功标识
    protected $request_success = true;
    //定义三方接口请求返回失败标识
    protected $request_error = false;
    //定义三方回调成功通知标识
    protected $callback_success = 'success';
    //定义三方回调失败通知标识
    protected $callback_error = 'fail';
    //定义三方配置
    protected $config = [];
    //定义三方错误提示信息
    protected $message = [];
    //默认三方配置
    const CONFIG = [
        'mch_id'       =>  '',//商户编号
        'mch_key'      =>  '',//支付秘钥
        'order_url'    =>  'http://api.whuser.com/api/out',//下单地址
        'notify_url'   =>  '',//回调通知地址
    ];
    const CALLBACK_PAY_STATUS = [];
    //配置三方状态码
    const MESSAGE = [];

    public function __construct($config = [])
    {
        $this->config = self::CONFIG;
        foreach ($config as $k => $v){
            if (isset($this->config[$k]) && !empty($v)){
                $this->config[$k] = $v;
            }
        }
    }

    /*代付汇款*/
    public function remit(WithdrawalOrder $order)
    {
        try{
            //初始化参数
            $cpClass = new HYSignature($this->config['mch_key']);

            $paramers['business']       = 'Transfer';//业务固定值: Transfer
            $paramers['business_type']  = 10101;//业务编码: 10101
            $paramers['api_sn']         = $order->withdrawalAuto->order_no;//Api订单号
            $paramers['notify_url']     = urlencode($this->config['notify_url']);//异步通知地址(需urlencode编码)
            $paramers['money']          = $order->money;//转账金额
            $paramers['bene_no']        = $order->card_no;//收款卡号
            $paramers['payee']          = urlencode($order->payee);//收款人(需urlencode编码)
            $paramers['timestamp']      = time();

            $bank_id = $this->getBank($order->bank_info);
            if ($bank_id === false){
                return ResponeFails('不支持的银行');
            }
            $paramers['bank_id']        = $bank_id;//收款银行卡编码(查看附bank_id)

            //获取签名字符串
            $sign_str = $cpClass->create_sign($paramers);
            $string = $sign_str . 'key=' . $this->config['mch_key'];
            //签名
            $paramers['sign'] = strtoupper(md5($string));
            $params = $cpClass->json_encode_ex($paramers);
            $data['params'] = base64_encode($cpClass->encrypt($params));
            $data['mcode'] = $this->config['mch_id'];
            $result = $this->curlPost($this->config['order_url'], $data);
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('daifu_send')->info('火蚁代付请求返回：'.$result['res']);
            if ($res['status'] != $this->request_success){
                //改为自动出款失败
                $this->automattcFails($order->id);
                return ResponeFails('代付平台下单失败');
            }
            //下单成功，订单状态改为自动出款中
            //WithdrawalAutomatic::where('order_id',$order->id)->update(['withdrawal_status'=>WithdrawalAutomatic::AUTOMATIC_PAYMENT]);
            return ResponeSuccess('代付平台发送申请成功');
        }catch (\Exception $exception){
            \Log::channel('daifu_send')->info('火蚁代付异常：'.$exception->getMessage());
            $this->automattcFails($order->id);
            return ResponeFails('下单申请异常');
        }

    }

    /**
     * post 请求
     * @param  string  $url  请求地址
     * @param  array  $data  请求参数
     * @param  integer  $timeOut  超时秒数
     * @return array           [int,string of json]
     */
    private function curlPost($url, $data, $timeOut = 30)
    {
        //启动一个CURL会话
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type'=>'application/x-www-form-urlencoded;charset=utf-8'));
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //要传送的所有数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // 执行操作
        $res = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ['responseCode' => $responseCode, 'res' =>$res];
    }

    /*回调*/
    public function callback()
    {
        try {
            $params       = request('params');
            $api_test = new HYDecode($params,$this->config['mch_key']);
            $data = $api_test->requestParamsDecode();//得到加密参数

            //查询订单(副表)
            $order = WithdrawalAutomatic::where('order_no',$data['api_sn'])->first();
            //判断订单是否存在
            if (!$order){
                \Log::channel('daifu_callback')->info('火蚁代付回调订单查询不存在');
                echo $this->callback_error;
                die;
            }
            //判断订单状态是否已经处理过成功了
            if ($order->withdrawal_status == WithdrawalAutomatic::AUTOMATIC_SUCCESS){
                echo $this->callback_success;
                die;
            }

            //验证签名
            if (!$api_test->verifySign($data, $data['sign'])) {
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('火蚁代付订单回调签名有误');
                echo $this->callback_error;
                die;
            }

            //判断打款是否成功
            if ($data['status'] == 50){
                //打款成功
                \DB::beginTransaction();
                //更改订单状态
                $withdrawalOrder                = withdrawalOrder::where('id', $order->order_id)->first();
                $withdrawalOrder->status        = WithdrawalOrder::PAY_SUCCESS;
                $withdrawalOrder->complete_time = date('Y-m-d H:i:s');
                $res1                           = $withdrawalOrder->save();
                //更改子状态
                $up_data['withdrawal_status'] = WithdrawalAutomatic::AUTOMATIC_SUCCESS;
                $up_data['third_order_no'] = $data['order_sn'];
                $res2 = WithdrawalAutomatic::where('order_id', $withdrawalOrder->id)->update($up_data);
                //清空用户当日打码量
                $res3 = GameScoreInfo::where('UserID', $withdrawalOrder->user_id)->update(['CurJettonScore' => 0]);
                if ($res1 && $res2 && $res3) {
                    \DB::commit();
                    //回调成功，通知代付平台
                    echo $this->callback_success;
                    die;
                } else {
                    \DB::rollBack();
                    //改为自动出款失败
                    $this->automattcFails($order->order_id);
                    \Log::channel('daifu_callback')->info('火蚁代付回调订单状态修改失败');
                    echo $this->callback_error;
                    die;
                }
            }else{
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('火蚁代付订单回调打款失败');
                echo $this->callback_error;
                die;
            }
        }catch (\Exception $exception){
            \DB::rollBack();
            //改为自动出款失败
            $this->automattcFails($order->order_id);
            \Log::channel('daifu_callback')->info('火蚁代付回调异常'.$exception->getMessage());
            echo $this->callback_error;
            die;
        }
    }

    //状态更改为自动出款失败
    private function automattcFails($order_id)
    {
        return WithdrawalAutomatic::where('order_id',$order_id)->update(['withdrawal_status'=>WithdrawalAutomatic::AUTOMATIC_FAILS]);
    }

    private function getBank($bank_name)
    {
        $bank_arr = [
            '-1' => '中国民生银行', '0' => '中国工商银行', '1' => '中国农业银行', '2' => '中国银行', '3' => '中国建设银行', '4' => '交通银行', '5' => '中信银行', '6' => '中国光大银行',
            '7' => '华夏银行', '8' => '广发银行', '9' => '平安银行', '10' => '招商银行', '11' => '兴业银行', '12' => '上海浦东发展银行', '13' => '北京银行', '14' => '天津银行',
            '15' => '河北银行', '17' => '邢台银行', '19' => '承德银行', '20' => '沧州银行', '21' => '廊坊银行', '22' => '衡水银行', '23' => '晋商银行', '24' => '晋城银行',
            '25' => '晋中银行', '26' => '内蒙古银行', '27' => '包商银行', '28' => '乌海银行', '29' => '鄂尔多斯银行', '30' => '大连银行', '32' => '锦州银行', '33' => '葫芦岛银行',
            '34' => '营口银行', '35' => '阜新银行', '36' => '吉林银行', '37' => '哈尔滨银行', '38' => '龙江银行', '39' => '南京银行', '40' => '江苏银行', '41' => '苏州银行',
            '43' => '杭州银行', '46' => '温州银行', '47' => '嘉兴银行', '48' => '湖州银行', '49' => '绍兴银行', '52' => '台州银行', '55' => '福建海峡银行', '56' => '厦门银行',
            '57' => '泉州银行', '58' => '南昌银行', '60' => '赣州银行', '61' => '上饶银行', '62' => '齐鲁银行', '63' => '青岛银行', '64' => '齐商银行', '65' => '枣庄银行',
            '66' => '东营银行', '67' => '烟台银行', '68' => '潍坊银行', '69' => '济宁银行', '71' => '莱商银行', '73' => '德州银行', '74' => '临商银行', '75' => '日照银行',
            '76' => '郑州银行', '77' => '中原银行', '78' => '洛阳银行', '79' => '平顶山银行', '81' => '汉口银行', '82' => '湖北银行', '83' => '华融湘江银行', '84' => '长沙银行',
            '85' => '广州银行', '86' => '珠海华润银行', '87' => '广东华兴银行', '88' => '广东南粤银行', '91' => '柳州银行', '97' => '德阳银行', '101' => '富滇银行', '104' => '西安银行',
            '105' => '长安银行', '106' => '兰州银行', '107' => '青海银行', '108' => '宁夏银行', '110' => '昆仑银行', '123' => '恒丰银行', '126' => '渤海银行', '127' => '徽商银行',
            '137' => '深圳前海微众银行', '138' => '上海银行', '143' => '鄞州银行', '145' => '福建省农村信用社', '160' => '中国邮政储蓄银行', '166' => '厦门国际银行',
        ];

        foreach ($bank_arr as $k => $v){
            if ($v == $bank_name){
                return $k;
            }
        }
        return false;
    }

}
