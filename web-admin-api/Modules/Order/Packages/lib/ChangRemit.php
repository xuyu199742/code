<?php

namespace Modules\Order\Packages\lib;

use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;
use Modules\Order\Packages\Signature\NormalSignature;
use Sco\Bankcard\Bankcard;


class ChangRemit extends Base
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
    //默认三方配置
    const CONFIG = [
        'mch_id'                =>  '',//商户id
        'mch_key'               =>  '',//支付秘钥
        'order_url'             =>  'https://e-sdk.omndbu.com/trade/order.api',//下单地址
        'fetch_url'             =>  'https://e-sdk.omndbu.com/trade/fetch.api',//查询订单地址
        'balance_url'           =>  'https://e-sdk.omndbu.com/trade/balance.api',//查询余额地址
        'bank_list_url'         =>  'https://e-sdk.omndbu.com/bank_list.api',//银行类型地址
        'notify_url'            =>  '',//回调通知地址
    ];
    const CALLBACK_PAY_STATUS = [
        '1'  => '已接单，待打款',
        '2'  => '打款中',
        '10' => '打款成功',
        '11' => '手动成功',
        '20' => '已撤销',
        '21' => '手动撤销',
        '30' => '错误',
    ];
    //配置三方状态码
    const MESSAGE = [
        '1'   => '请求成功',
        '100' => '缺少必要的参数',
        '101' => '检验参数错误',
        '102' => '找不到对应的商户,或被被限制',
        '103' => '订单号不存在',
        '110' => '查询异常',
        '111' => '请求方法异常',
        '112' => '没有可用支付通道',
        '113' => '系统API异常',
        '114' => '系统下单异常或支付通',
        '115' => '打款异常',
    ];

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
        //银行判断
        try{
            $bankcard = new Bankcard();
            $info = $bankcard->info($order->card_no);
            //判断卡类型，仅储值卡可以用
            if ($info->getCardType() != 'DC'){
                //改为自动出款失败
                $this->automattcFails($order->id);
                return ResponeFails('打款仅支持非储值卡');
            }
            //代号映射
            $bank_id = 'NOFOUND';//默认未知银行
            foreach ($this->brankMap() as $k => $v){
                if ($k == $info->getBankCode()){
                    $bank_id = $v;
                }
            }
            //dd($info->getBankCode());
        }catch (\Exception $exception){
            //改为自动出款失败
            $this->automattcFails($order->id);
            return ResponeFails('银行卡号输入有误');
        }
        try{
            //查询余额吗，请求参数
            $balance_data['mch_id']     = $this->config['mch_id'];
            $balance_data['created_at'] = time();
            $balance_data['sign_type']  = 'md5';
            $balance_data['mch_key']    = $this->config['mch_key'];
            //签名
            $balance_data['sign']              = NormalSignature::encrypt($balance_data);
            //发送请求
            unset($balance_data['mch_key']);
            $balance_info = $this->curlPost($this->config['balance_url'],$balance_data);
            $res = json_decode($balance_info['res'], true); //解析返回结果
            //dd($balance_data,$balance_info,$res);
            if ($res['code'] != $this->request_success){
                //改为自动出款失败
                $this->automattcFails($order->id);
                return ResponeFails('代付平台余额查询失败');
            }
            //判断账号余额是否充足
            if ($res['data']['balance'] < $order->money){
                //改为自动出款失败
                $this->automattcFails($order->id);
                return ResponeFails('代付平台余额不足');
            }

            //请求参数
            $data['mch_id']            = $this->config['mch_id'];//商户ID，由平台分配
            //$data['mch_order']         = $order->order_no;//订单号商家系统创建-可用于查询订单
            $data['mch_order']         = $order->withdrawalAuto->order_no;//由于有字段限制用副表的短订单号
            $data['client_ip']         = getIp();//客户端IP地址 例：127.0.0.1
            $data['amt']               = $order->money;//订单金额,单位元 如0.01
            $data['notify_url']        = $this->config['notify_url'];//订单回调通知URL-必须能正常访问
            $data['bank_id']           = $bank_id;//银行code通过银行接口列表获取，如工商银行-ICBC未知银行 - NOFOUND
            $data['bank_card']         = $order->card_no;//银行卡号- 填写错误将无法打款
            $data['real_name']         = $order->payee;//开户人姓名 - 填写错误将无法打款
            $data['sign_type']         = 'md5';//默认md5 - 详情参考接口安全说明
            $data['created_at']        = time();//订单创建时间 Unix时间戳 例：1565011594
            //$data['bank_card_type']    = '1';//1.对私账户 2对公账户 默认对私
            //$data['bank_open_address'] = '';//开户行地址 ，对公必填
            //$data['remark']            = '';//备注
            //签名
            $data['mch_key']           = $this->config['mch_key'];//订单创建时间 Unix时间戳 例：1565011594
            $data['sign']              = NormalSignature::encrypt($data);//MD5签名结果，详见“sign安全规范”
            //发送请求
            unset($data['mch_key']);
            $result = $this->curlPost($this->config['order_url'],$data);
            $res = json_decode($result['res'], true); //解析返回结果
            \Log::channel('daifu_send')->info('畅代付请求返回：'.$result['res']);

            if ($res['code'] != $this->request_success){
                //改为自动出款失败
                $this->automattcFails($order->id);
                \Log::channel('daifu_send')->info('畅代付下单失败问题：'.self::MESSAGE[$res['code']].$result['res']);
                return ResponeFails('代付平台下单失败');
            }
            //下单成功，订单状态改为自动出款中
            //WithdrawalAutomatic::where('order_id',$order->id)->update(['withdrawal_status'=>WithdrawalAutomatic::AUTOMATIC_PAYMENT]);
            return ResponeSuccess('代付平台发送申请成功');
        }catch (\Exception $exception){
            \Log::channel('daifu_send')->info('畅代付异常：'.$exception->getMessage());
            $this->automattcFails($order->id);
            return ResponeFails('下单申请异常');
        }

    }

    /*回调*/
    public function callback()
    {
        //查询订单(副表)
        $order = WithdrawalAutomatic::where('order_no',request('mch_order'))->first();
        //判断订单是否存在
        if (!$order){
            \Log::channel('daifu_callback')->info('畅代付回调订单查询不存在');
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
            $data['mch_id']     = request('mch_id');//下单请求时的商户id
            $data['mch_order']  = request('mch_order');//下单请求时的订单号 ；如果是重复的补充订单则不是下单时的订单号-为上游支付订单号
            $data['amt']        = request('amt');//实际的充值金额,单位元 如0.01
            $data['amt_type']   = request('amt_type');//币种类型 cny:人民币
            $data['sign_type']  = request('sign_type');//默认md5 - 详情参考接口安全说明
            $data['status']     = request('status');//状态值 1 打款中 10 打款成功 11 手动成功 20 已撤销 21手动撤销 30 错误
            $data['created_at'] = request('created_at');//时间戳
            $data['success_at'] = request('success_at');//时间戳 - 支付成功时候才有值 空值时候在签名sign中要排除
            $data['mch_key']    = $this->config['mch_key'];
            $sign               = request('sign');
            //验证签名
            if ($sign != NormalSignature::encrypt($data)) {
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('畅代付订单回调签名有误');
                echo $this->callback_error;
                die;
            }
            //判断打款是否成功
            if ($data['status'] == 1){
                //打款中不做更改，返回fail继续回调
                \Log::channel('daifu_callback')->info('畅代付订单回调打款中');
                echo $this->callback_error;
                die;
            }elseif (in_array($data['status'],[10,11])){
                //打款成功
                \DB::beginTransaction();
                //更改订单状态
                $withdrawalOrder                = withdrawalOrder::where('id', $order->order_id)->first();
                $withdrawalOrder->status        = WithdrawalOrder::PAY_SUCCESS;
                $withdrawalOrder->complete_time = date('Y-m-d H:i:s');
                $res1                           = $withdrawalOrder->save();
                //更改子状态
                $up_data['withdrawal_status'] = WithdrawalAutomatic::AUTOMATIC_SUCCESS;
                $up_data['third_order_no'] = $data['mch_order'];
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
                    \Log::channel('daifu_callback')->info('畅代付回调订单状态修改失败');
                    echo $this->callback_error;
                    die;
                }
            }else{
                //改为自动出款失败
                $this->automattcFails($order->order_id);
                \Log::channel('daifu_callback')->info('畅代付订单回调打款失败');
                echo $this->callback_error;
                die;
            }
        }catch (\Exception $exception){
            \DB::rollBack();
            //改为自动出款失败
            $this->automattcFails($order->order_id);
            \Log::channel('daifu_callback')->info('畅代付回调异常'.$exception->getMessage());
            echo $this->callback_error;
            die;
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
        return ['responseCode' => $responseCode, 'res' => $res];
    }

    //状态更改为自动出款失败
    private function automattcFails($order_id)
    {
        return WithdrawalAutomatic::where('order_id',$order_id)->update(['withdrawal_status'=>WithdrawalAutomatic::AUTOMATIC_FAILS]);
    }

    /*查询订单*/
    /*public function fetchOrder()
    {
        //请求参数
        $data['mch_id']      = $this->config['mch_id'];
        $data['mch_order']   = '';
        $data['created_at'] = time();
        $data['sign_type'] = 'md5';
        //签名
        $data['sign']              = NormalSignature::encrypt($data,'&mch_key='.$this->config['mch_key']);
        //发送请求
        $res = $this->curlPost($this->config['order_url'],$data);
        return $res;
    }*/

    /*查询余额*/
    /*public function fetchBalance()
    {
        //请求参数
        $data['mch_id']      = $this->config['mch_id'];
        $data['created_at'] = time();
        $data['sign_type'] = 'md5';
        //签名
        $data['sign']              = NormalSignature::encrypt($data,'&mch_key='.$this->config['mch_key']);
        //发送请求
        $res = $this->curlPost($this->config['order_url'],$data);
        return $res;
    }*/

    /*银行代号获取*/
    /*public function bankList()
    {
        $bankList = file_get_contents($this->config['bank_list_url']."?mch_id={$this->config['mch_id']}");
        $bankList = json_decode($bankList);
        return $bankList;
    }*/

    /*const BANK_TYPE = [
        'NOFOUND'   =>  '未知银行', 'ICBC'      =>  '工商银行',     'ABC'       =>  '农业银行',       'CMB'         =>  '招商银行',
        'CCB'       =>  '建设银行', 'CMBC'      =>  '民生银行',     'BOC'       =>  '中国银行',       'BCM'         =>  '交通银行',
        'CIB'       =>  '兴业银行', 'CEB'       =>  '光大银行',     'GDB'       =>  '广东发展银行',   'PSBC'        =>  '邮政储蓄银行',
        'CNCB'      =>  '中信银行', 'SPDB'      =>  '浦发银行',     'PAB'       =>  '平安银行',       'HXB'         =>  '华夏银行',
        'SHB'       =>  '上海银行', 'BOB'       =>  '北京银行',     'HSBC'      =>  '汇丰银行',       'RCC'         =>  '农村信用社',
        'NBCB'      =>  '宁波银行', 'NJCB'      =>  '南京银行',     'JSB'       =>  '江苏银行',       'DONGGB'      =>  '东莞银行',
        'CQCB'      =>  '重庆银行', 'TCCB'      =>  '天津银行',     'EGB'       =>  '恒丰银行',       'CZB'         =>  '浙商银行',
        'CBHB'      =>  '渤海银行', 'HEBB'      =>  '河北银行',     'BOIMC'     =>  '内蒙古银行',     'JSHB'        =>  '晋商银行',
        'JLB'       =>  '吉林银行', 'HRBCB'     =>  '哈尔滨银行',    'HZB'      =>  '杭州银行',       'WZCB'        =>  '温州银行',
        'XMCCB'     =>  '厦门银行', 'QZCCB'     =>  '泉州银行',     'NCHCB'     =>  '南昌银行',       'JJCCB'       =>  '九江银行',
        'QLB'       =>  '齐鲁银行', 'JNB'       =>  '济宁银行',     'QDCCB'     =>  '青岛银行',       'LSBC'        =>  '临商银行',
        'WFCCB'     =>  '潍坊银行', 'YTAIB'     =>  '烟台银行',     'BORZ'      =>  '日照银行',       'LSB'         =>  '莱商银行',
        'ZZB'       =>  '郑州银行', 'BOLUOY'    =>  '洛阳银行',     'HKOUB'     =>  '汉口银行',       'HUBEIB'      =>  '湖北银行',
        'CSCB'      =>  '长沙银行', 'GZCB'      =>  '广州银行',     'SRCB'      =>  '上海农商银行',   'SDB'         =>  '深圳发展银行',
        'DRCBANK'   =>  '东莞农村', 'SDEBANK'   =>  '顺德农商',     'BOBBG'     =>  '广西北部湾银行', 'TLBANK'      =>  '浙江泰隆商业银行',
        'ZJMTBANK'  =>  '浙江民泰商业银行'
    ];*/

    //银行映射,拓展包与三方银行映射
    public function brankMap()
    {
        return [
            'ICBC'    =>  'ICBC',//中国工商银行
            'BOC'    =>  'BOC',//中国银行
            'CCB'    =>  'CCB',//建设银行
            'ABC'    =>  'ABC',//农业银行
            'COMM'    =>  'BCM',//交通银行
            'PSBC'    =>  'PSBC',//邮政储蓄银行
            'CMB'    =>  'CMB',//招商银行
            'SPABANK'    =>  'PAB',//平安银行
            'CMBC'    =>  'CMBC',//民生银行
            'CEB'    =>  'CEB',//光大银行
            'HXBANK'    =>  'HXB',//华夏银行
            'CITIC'    =>  'CNCB',//中信银行
            'SPDB'    =>  'SPDB',//浦发银行
            'GDB'    =>  'GDB',//广发银行
            'CIB'    =>  'CIB',//兴业银行
            'BJBANK'    =>  'BOB',//北京银行
            'BOHAIB'    =>  'CBHB',//渤海银行
            'JSBANK'    =>  'JSB',//江苏银行
            'EGBANK'    =>  'EGB',//恒丰银行
        ];
    }

}
