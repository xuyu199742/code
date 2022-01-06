<?php

namespace Modules\Order\Packages\lib;

use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;
use Modules\Order\Packages\Signature\XinSignature;

class XinRemit extends Base
{
    //定义三方接口请求返回成功标识
    protected $request_success = 'success';
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
            //请求参数
            //$data['orderid']  = $order->order_no;//订单号商家系统创建-可用于查询订单
            $data['orderid']    = $order->withdrawalAuto->order_no;//由于有字段限制用副表的短订单号
            $data['b_num']      = $order->card_no;//银行卡号- 填写错误将无法打款
            $data['b_user']     = $order->payee;//开户人姓名 - 填写错误将无法打款
            $data['b_type']     = '中国银行';//$bank_name;//银行类型，例：工商银行，建设银行
            $data['amount']     = $order->money;//订单金额,单位元 如0.01
            $data['remarks']    = '用户提现代付';//备注
            //参数拼接
            $param = '';
            foreach ($data as $key => $val) {
                $param = $param . $key .'=' . $val . '!';
            }
            $param = urlencode(rtrim($param, '!'));
            //des加密
            $param = XinSignature::encrypt($param,$this->config['mch_key']);
            //拼接url
            $url = $this->config['order_url'].'?param='.$param.'&code='.$this->config['mch_id'];
            //发送请求
            $result = $this->curlPost($url,$data);
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('daifu_send')->info('新代付请求返回：'.$result['res']);

            if ($res['errMsg'] != $this->request_success){
                //改为自动出款失败
                $this->automattcFails($order->id);
                return ResponeFails('代付平台下单失败');
            }
            //下单成功，订单状态改为自动出款中
            //WithdrawalAutomatic::where('order_id',$order->id)->update(['withdrawal_status'=>WithdrawalAutomatic::AUTOMATIC_PAYMENT]);
            return ResponeSuccess('代付平台发送申请成功');
        }catch (\Exception $exception){
            \Log::channel('daifu_send')->info('新代付异常：'.$exception->getMessage());
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
        $order = WithdrawalAutomatic::where('order_no',request('mch_order'))->first();
        //判断订单是否存在
        if (!$order){
            \Log::channel('daifu_callback')->info('新代付回调订单查询不存在');
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
            $data['orderid']    = request('orderid');//	订单号
            $data['amount']     = request('amount');//支付金额
            $data['backamount'] = request('backamount');//金额,订单金额
            $data['remarks']    = request('remarks');//申请时候的备注信息
            $data['state']      = request('state');//	订单状态：1，成功。2，失败。0，待处理
            $data['msg']        = request('msg');//审核信息
            $data['pwd']        = request('pwd');//MD5(orderid + remarks),可做验证用
            $data['errMsg']     = request('errMsg');//申请成功返回：success
            //验证签名
            if ($data['pwd'] != md5($data['orderid'].$data['remarks'])) {
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('新代付订单回调签名有误');
                echo $this->callback_error;
                die;
            }
            //判断打款是否成功
            if ($data['state'] == 1){
                //打款成功
                \DB::beginTransaction();
                //更改订单状态
                $withdrawalOrder                = withdrawalOrder::where('id', $order->order_id)->first();
                $withdrawalOrder->status        = WithdrawalOrder::PAY_SUCCESS;
                $withdrawalOrder->complete_time = date('Y-m-d H:i:s');
                $res1                           = $withdrawalOrder->save();
                //更改子状态
                $up_data['withdrawal_status'] = WithdrawalAutomatic::AUTOMATIC_SUCCESS;
                $up_data['third_order_no'] = $data['orderid'];
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
                    \Log::channel('daifu_callback')->info('新代付回调订单状态修改失败');
                    echo $this->callback_error;
                    die;
                }
            }else{
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('新代付订单回调打款失败');
                echo $this->callback_error;
                die;
            }
        }catch (\Exception $exception){
            \DB::rollBack();
            //改为自动出款失败
            $this->automattcFails($order->order_id);
            \Log::channel('daifu_callback')->info('新代付回调异常'.$exception->getMessage());
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
