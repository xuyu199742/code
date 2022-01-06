<?php

namespace Modules\Order\Packages\lib;

use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;
use Modules\Order\Packages\Signature\NormalSignature;

class LXRemit extends Base
{
    //定义三方接口请求返回成功标识
    protected $request_success = 1;
    //定义三方接口请求返回失败标识
    protected $request_error = '';
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
        'mch_id'                =>  '',//商户编号13914
        'mch_key'               =>  '',//支付秘钥0d099fe02c76b666ec5a219dcf140099458859bd
        'order_url'             =>  'http://27.124.36.47/apidaifu',//下单地址
        'notify_url'            =>  '',//回调通知地址
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
            //请求参数
            $data['customerid']     = $this->config['mch_id'];//商户编号
            $data['realname']       = $order->payee;//真实姓名
            $data['money']          = intval($order->money * 100);//单位为分
            $data['bank_name']      = $order->bank_info;//选填(转账到支付宝账号可不填)例如:中国建设银行
            $data['card_number']    = $order->card_no;//银行卡号或者支付宝账号
            $data['out_trade_no']   = $order->withdrawalAuto->order_no;//订单号
            $data['notifyurl']      = $this->config['notify_url'];//回调地址
            $data['type']           = 1;//账户类型 1 银行卡 2 支付宝 默认为 1
            //$data['phone']          = "";//手机号 选填
            //$data['idcard']         = "";//身份证号 选填

            $mch_key        = '&key=' . $this->config['mch_key'];
            $data['sign']   = NormalSignature::encrypt($data,$mch_key);

            //发送请求
            $result = $this->curlPost($this->config['order_url'],$data);
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('daifu_send')->info('龙鑫代付请求返回：'.$result['res']);

            if ($res['status'] != $this->request_success){
                //改为自动出款失败
                $this->automattcFails($order->id);
                return ResponeFails('代付平台下单失败');
            }
            //下单成功，订单状态改为自动出款中
            //WithdrawalAutomatic::where('order_id',$order->id)->update(['withdrawal_status'=>WithdrawalAutomatic::AUTOMATIC_PAYMENT]);
            return ResponeSuccess('代付平台发送申请成功');
        }catch (\Exception $exception){
            \Log::channel('daifu_send')->info('龙鑫代付异常：'.$exception->getMessage());
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
        //查询订单(副表)
        $order = WithdrawalAutomatic::where('order_no',request('out_trade_no'))->first();
        //判断订单是否存在
        if (!$order){
            \Log::channel('daifu_callback')->info('龙鑫代付回调订单查询不存在');
            echo $this->callback_error;
            die;
        }
        //判断订单状态是否已经处理过成功了
        if ($order->withdrawal_status == WithdrawalAutomatic::AUTOMATIC_SUCCESS){
            echo $this->callback_success;
            die;
        }
        try {
            //回调数据重组
            $data['customerid']     = request('customerid');//商户编号
            $data['money']          = request('money');//代付金额，实际代付金额 单位分
            $data['order_no']       = request('order_no');//系统订单号
            $data['status']         = request('status');//代付状态 1 成功 2 失败
            $data['out_trade_no']   = request('out_trade_no');//订单号
            $data['pay_time']       = request('pay_time');//代付时间 时间戳
            $data['fee']            = request('fee');//代付手续费 单位分
            $sign = request('sign');
            $mch_key        = '&key=' . $this->config['mch_key'];
            //验证签名
            if ($sign != NormalSignature::encrypt($data,$mch_key)) {
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('畅代付订单回调签名有误');
                echo $this->callback_error;
                die;
            }

            //判断打款是否成功
            if ($data['status'] == 1){
                //打款成功
                \DB::beginTransaction();
                //更改订单状态
                $withdrawalOrder                = withdrawalOrder::where('id', $order->order_id)->first();
                $withdrawalOrder->status        = WithdrawalOrder::PAY_SUCCESS;
                $withdrawalOrder->complete_time = date('Y-m-d H:i:s');
                $res1                           = $withdrawalOrder->save();
                //更改子状态
                $up_data['withdrawal_status'] = WithdrawalAutomatic::AUTOMATIC_SUCCESS;
                $up_data['third_order_no'] = $data['out_trade_no'];
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
                    \Log::channel('daifu_callback')->info('龙鑫代付回调订单状态修改失败');
                    echo $this->callback_error;
                    die;
                }
            }else{
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('龙鑫代付订单回调打款失败');
                echo $this->callback_error;
                die;
            }
        }catch (\Exception $exception){
            \DB::rollBack();
            //改为自动出款失败
            $this->automattcFails($order->order_id);
            \Log::channel('daifu_callback')->info('龙鑫代付回调异常'.$exception->getMessage());
            echo $this->callback_error;
            die;
        }
    }

    //状态更改为自动出款失败
    private function automattcFails($order_id)
    {
        return WithdrawalAutomatic::where('order_id',$order_id)->update(['withdrawal_status'=>WithdrawalAutomatic::AUTOMATIC_FAILS]);
    }

}
