<?php

namespace Modules\Payment\Http\Controllers;

use App\Jobs\MsgPush;
use App\Rules\ProviderExist;
use App\Rules\UserExist;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\PaymentPassageway;
use Models\AdminPlatform\PaymentProvider;
use Models\AdminPlatform\PaymentWay;
use Models\AdminPlatform\RechargeAgent;
use Models\AdminPlatform\RechargeAlipay;
use Models\AdminPlatform\RechargeUnion;
use Models\AdminPlatform\RechargeWechat;
use Modules\Payment\Packages\ThirdPay\Facades\Pay;
use Modules\Payment\Packages\ThirdPay\Interfaces\PayTypes;
use Validator;

class PaymentController extends Controller
{
    private $limit  = ['ju_ren_pay'];
    //支付方式列表和支付通道列表
    public function paymentsList($game_id)
    {
        //VIP等级
        $user_vip = AccountsInfo::where('GameID', $game_id)->value('MemberOrder');
        $list_key = $user_vip;
        //是否第一次充值
        $p_count = PaymentOrder::where('game_id', $game_id)->where('payment_status', PaymentOrder::SUCCESS)->count();
        $list = [];
        $list = PaymentWay::where('status', PaymentWay::ON)->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->get();
        $category = [];
        $channel = [];
        $types = [];
        foreach ($list as $k => $v) {
            //官方充值
            if ($v->type == 2 && $v->status == PaymentWay::ON) {
                foreach ($list as $n) {
                    if ($n->type == 0 && $n->status == PaymentWay::ON) {
                        $pay_type = $n['pay_type'];
                        if ($pay_type == RechargeWechat::SIGN) {
                            $wechat = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')
                                ->leftJoin(RechargeWechat::tableName() . ' AS b', 'a.pid', '=', 'b.id')
                                ->where('a.table_type', 3)
                                ->where('a.status', PaymentPassageway::ON)
                                ->where(function ($query) use ($p_count, $list_key) {
                                    if ($p_count > 0) {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    } else {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere(\DB::raw("(a.authority & 1)"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    }
                                })
                                ->orderBy('a.sort', 'asc')
                                ->get();
                            if ($wechat && count($wechat) > 0) {
                                $channel['official'][$pay_type] = $n->toArray();
                                $channel['official'][$pay_type]['pay_list'] = collect($wechat->toArray())->map(function ($item) {
                                    if ($item['code_address']) {
                                        $item['code_address'] = cdn($item['code_address']);
                                    }
                                    return $item;
                                })->toArray();
                            } else {
                                unset($list[$k]);
                            }
                        }
                        if ($pay_type == RechargeAlipay::SIGN) {
                            $alipay = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')
                                ->leftJoin(RechargeAlipay::tableName() . ' AS b', 'a.pid', '=', 'b.id')
                                ->where('a.table_type', 4)
                                ->where('a.status', PaymentPassageway::ON)
                                ->where(function ($query) use ($p_count, $list_key) {
                                    if ($p_count > 0) {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    } else {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere(\DB::raw("(a.authority & 1)"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    }
                                })
                                ->orderBy('a.sort', 'asc')
                                ->get();
                            if ($alipay && count($alipay) > 0) {
                                $channel['official'][$pay_type] = $n->toArray();
                                $channel['official'][$pay_type]['pay_list'] = collect($alipay->toArray())->map(function ($item) {
                                    if ($item['code_address']) {
                                        $item['code_address'] = cdn($item['code_address']);
                                    }
                                    return $item;
                                })->toArray();
                            } else {
                                unset($list[$k]);
                            }
                        }
                        if ($pay_type == RechargeUnion::SIGN) {
                            $union = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')
                                ->leftJoin(RechargeUnion::tableName() . ' AS b', 'a.pid', '=', 'b.id')
                                ->where('a.table_type', 2)
                                ->where('a.status', PaymentPassageway::ON)
                                ->where(function ($query) use ($p_count, $list_key) {
                                    if ($p_count > 0) {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    } else {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere(\DB::raw("(a.authority & 1)"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    }
                                })
                                ->orderBy('a.sort', 'asc')
                                ->get();
                            if ($union && count($union) > 0) {
                                $channel['official'][$pay_type] = $n->toArray();
                                $channel['official'][$pay_type]['pay_list'] = $union->toArray();
                            } else {
                                unset($list[$k]);
                            }
                        }
                        if ($pay_type == RechargeAgent::SIGN) {
                            $agent = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')
                                ->leftJoin(RechargeAgent::tableName() . ' AS b', 'a.pid', '=', 'b.id')
                                ->where('a.table_type', 1)
                                ->where('a.status', PaymentPassageway::ON)
                                ->where(function ($query) use ($p_count, $list_key) {
                                    if ($p_count > 0) {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    } else {
                                        $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                            ->orWhere(\DB::raw("(a.authority & 1)"), '>', 0)
                                            ->orWhere('a.frequency', '>', $p_count);
                                    }
                                })
                                ->orderBy('a.sort', 'asc')
                                ->get();
                            if ($agent && count($agent) > 0) {
                                $channel['official'][$pay_type] = $n->toArray();
                                $channel['official'][$pay_type]['pay_list'] = $agent->toArray();
                            } else {
                                unset($list[$k]);
                            }
                        }
                    }
                }
                if (isset($channel['official']) && count($channel['official']) > 0) {
                    $category[$v->pay_type] = $v->toArray();
                }
            } else {
                //三方充值
                if ($v->type != 0) {
                    $third_pay = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')
                        ->leftJoin(PaymentProvider::tableName() . ' AS b', 'a.pid', '=', 'b.id')
                        ->where('b.pay_type', $v->pay_type)
                        ->where('a.table_type', 5)
                        ->where('a.status', PaymentPassageway::ON)
                        ->where(function ($query) use ($p_count, $list_key) {
                            if ($p_count > 0) {
                                $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                    ->orWhere('a.frequency', '>', $p_count);
                            } else {
                                $query->where(\DB::raw("(a.authority & " . PaymentPassageway::VIP_LISTS[$list_key] . ")"), '>', 0)
                                    ->orWhere(\DB::raw("(a.authority & 1)"), '>', 0)
                                    ->orWhere('a.frequency', '>', $p_count);
                            }
                        })
                        ->orderBy('a.sort', 'asc')
                        ->get();
                    if ($third_pay && count($third_pay) > 0) {
                        // 客户端：限制最多传8个支付通道
                        $tmp = $third_pay->toArray();
                        foreach ($tmp as $key => $val) {
                            if (isset($val['pay_value']) && !empty($val['pay_value'])) {
                                //  去重 | 正序 | 限制8个
                                $arr = array_slice(array_unique(explode(",", $val['pay_value'])), 0, 8);
                                sort($arr);
                                $tmp[$key]['pay_value'] = implode(",", $arr);
                                $tmp[$key]['range']=$tmp[$key]['range'] == PaymentProvider::ON ? true : false;
                            }
                        }
                        $category[$v->pay_type] = $v->toArray();
                        $channel[$v->pay_type] = $tmp;
                    }
                }
            }
        }
        $result['level1'] = $category;
        $result['level2'] = $channel;
        if ($result && count($result) > 0 && system_configs('coin.recharge_status')) {
            return ResponeSuccess('充值开启', $result);
        }
        return ResponeFails('充值关闭');
    }

    //支付列表
    public function types()
    {
        $list = [];
        //四方支付
        $third = Pay::randomChannels();//分组内通道随机获取一条
        //$third = Pay::payChannels();//分组内通道获取所有,并组重组里面的数据集合为一条，发到客户端标识无效，对于客户端发送支付请求需要随机判断
        if ($third) {
            $list['third_pay'] = $third;
        }
        //添加官方支付
        $wechat = RechargeWechat::where('state', RechargeWechat::ON)->orderBy('sort', 'desc')->get();
        if ($wechat && count($wechat) > 0) {
            $list[RechargeWechat::SIGN] = [
                'name' => '官方微信',
                'type' => 'wechat',
                'provider_type' => RechargeWechat::SIGN,
                'special' => true,
                'url' => asset('storage'),
                'data' => collect($wechat->toArray())->map(function ($item) {
                    if ($item['code_address']) {
                        $item['code_address'] = cdn('storage/' . $item['code_address']);
                    }
                    return $item;
                })->toArray()
            ];
        }
        $alipay = RechargeAlipay::where('state', RechargeAlipay::ON)->orderBy('sort', 'desc')->get();
        if ($alipay && count($alipay) > 0) {
            $list[RechargeAlipay::SIGN] = [
                'name' => '官方支付宝',
                'type' => 'alipay',
                'provider_type' => RechargeAlipay::SIGN,
                'special' => true,
                'url' => asset('storage'),
                'data' => collect($alipay->toArray())->map(function ($item) {
                    if ($item['code_address']) {
                        $item['code_address'] = asset('storage/' . $item['code_address']);
                    }
                    return $item;
                })->toArray()
            ];
        }
        $union = RechargeUnion::where('state', RechargeUnion::ON)->orderBy('sort', 'desc')->get();
        if ($union && count($union) > 0) {
            $list[RechargeUnion::SIGN] = [
                'name' => '官方银联',
                'type' => 'union',
                'provider_type' => RechargeUnion::SIGN,
                'special' => true,
                'data' => $union->toArray()
            ];
        }
        $agent = RechargeAgent::where('state', RechargeAgent::ON)->orderBy('sort', 'desc')->get();
        if ($agent && count($agent) > 0) {
            $list[RechargeAgent::SIGN] = [
                'name' => '代理',
                'type' => 'agent',
                'provider_type' => RechargeAgent::SIGN,
                'special' => true,
                'data' => $agent->toArray()
            ];
        }
        if ($list && count($list) > 0 && system_configs('coin.recharge_status')) {
            return ResponeSuccess('充值开启', $list);
        }
        return ResponeFails('充值关闭');
    }

    //发起支付
    public function pay()
    {
        if (!system_configs('coin.recharge_status')) {
            return ResponeFails('充值通道关闭');
        }
        $type = request('type');
        if ($type && in_array($type, array_keys(PaymentOrder::OFFICIAL))) {
            //官方支付
            if($this->getOfficeApproval($type) == false) return ResponeFails('当前通道关闭或受限，请选用其它支付通道');
            return $this->officialOrder();
        }
        if ($type && in_array($type, array_keys(PaymentOrder::CHANNEL))) {
            //渠道商支付
            return $this->channel_pay();
        }
        $provider = PaymentProvider::find(request('type'));
        if (in_array($provider->config->key,$this->limit)) {
            //限制30秒内不可重复请求
            if (!reqAstrict(request('user_id'), 30)) {
                return ResponeFails('充值通道频率限制,请30秒后再试');
            }
        }
        //四方支付判断是否有权限支付
        if($this->getApproval(request('user_id'), $type) == false) return ResponeFails('当前通道关闭或受限，请选用其它支付通道');
        if (Pay::send()) {
            $order = Pay::order();
            //外部充值订单消息后台推送
            MsgPush::dispatch(['type' => 'thirdPay']);
            if($order->payment_type == PayTypes::ALIPAY_SDK){
                //sdk支付
                $sdk=Pay::getSdk();
                if($sdk){
                    return ResponeSuccess('订单提交成功,请支付', ['type'=>1,'uri' => $sdk, 'order_no' => $order->order_no]);
                }
                return ResponeFails('充值失败,请30秒后再试');
            }

            $order_page = url('api/payment/order/' . $order->order_no);
            if (env("PAY_CALLBACK_URL")) {
                $order_page = env("PAY_CALLBACK_URL") . '/api/payment/order/' . $order->order_no;
            }
            return ResponeSuccess('订单提交成功,请支付', ['type'=>0,'uri' => $order_page, 'order_no' => $order->order_no]);
        }
        return ResponeFails('充值通道不稳定,请30秒后再试');
    }

    // 官方获取允许支付权限
    private function getOfficeApproval($type) :bool {
        $chan = PaymentWay::where('pay_type', $type)->select('status')->first();
        if($chan['status'] == PaymentWay::OFF) return false;
        return true;
    }

    // 四方获取允许支付权限 // 1、通道状态status 2、vip权限authority 3、次数frequency
    private function getApproval($user_id,$type) :bool {
        $chan = PaymentPassageway::from(PaymentPassageway::tableName().' as a')
            ->join(PaymentWay::tableName().' as b','a.wid','=','b.id')
            ->where('a.table_type',5)
            ->where('a.pid',$type)
            ->select('a.status','a.authority','a.frequency','b.status as statusAll')
            ->first();
        // 1、判断状态
        if($chan['status'] == PaymentPassageway::OFF || $chan['statusAll'] == PaymentWay::OFF) return false;
        // 2、vip权限authority
        //VIP等级
        $user_vip = AccountsInfo::where('UserID', $user_id)->value('MemberOrder');
        if($chan['authority'] & PaymentPassageway::VIP_LISTS[$user_vip]) return true;
        //首充次数和通道充值次数
        $first = PaymentOrder::where('user_id', $user_id)->where('payment_status', PaymentOrder::SUCCESS)->count();
        if($first == 0 && $chan['authority'] & 1) return true;
        // 3、次数frequency
        if($first < $chan['frequency']) {
            return true;
        } else {
            $count = PaymentOrder::where('user_id', $user_id)->where('payment_status', PaymentOrder::SUCCESS)->where('payment_provider_id', $type)->count();
            if($count < $chan['frequency']) return true;
        }
        return false;
    }

    //官方支付
    private function officialOrder()
    {
        $model = new PaymentOrder();
        Validator::make(request()->all(), [
            'user_id' => ['required', new UserExist('pay')],
            'money' => ['required', 'numeric', 'max:999999'],
            'third_order_no' => ['required'],
        ], [
            'user_id.required' => '用户标识不能为空',
            'money.required' => '充值' . config('set.amount') . '不能为空',
            'money.numeric' => '充值' . config('set.amount') . '必须是数字',
            'money.max' => '充值' . config('set.amount') . '超过上限',
            'third_order_no.required' => '订单号必填',
        ])->validate();
        try {
            if ($model->saveOfficialOrder()) {
                //内部充值订单消息后台推送
                MsgPush::dispatch(['type' => 'officialPay']);
                return ResponeSuccess('订单提交成功,即将到账，请耐心等待', ['uri' => '']);
            }
        } catch (\Exception $e) {
            \Log::error('生成订单失败:' . $e->getMessage());
        }
        return ResponeFails('充值失败,请联系客服');
    }

    //渠道支付,小米、360。。。。etc
    private function channel_pay()
    {
        $model = new PaymentOrder();
        Validator::make(request()->all(), [
            'user_id' => ['required', new UserExist('pay')],
            'money' => ['required', 'numeric', 'max:999999'],
        ], [
            'user_id.required' => '用户标识不能为空',
            'money.required' => '充值' . config('set.amount') . '不能为空',
            'money.numeric' => '充值' . config('set.amount') . '必须是数字',
            'money.max' => '充值' . config('set.amount') . '超过上限',
        ])->validate();
        try {
            if ($model->saveChannelOrder()) {
                //外部充值订单消息后台推送
                MsgPush::dispatch(['type' => 'thirdPay']);
                return ResponeSuccess('订单提交成功,即将到账，请耐心等待', ['uri' => url('api/payment/order/' . $model->order_no), 'order_no' => $model->order_no]);
            }
        } catch (\Exception $e) {
            \Log::error('生成订单失败:' . $e->getMessage());
        }
        return ResponeFails('充值失败,请联系客服');
    }

    //支付页面
    public function order($order_no)
    {
        return Pay::view($order_no);
    }

    //充值回调
    public function callback($skd_name)
    {
        return pay::callback($skd_name);
    }

    //四方测试请求回调
    public function sf_test_callback()
    {
        $callback_data = request()->all();
        if (!request('order_no') || !request('notify_url')) {
            return 'fail';
        }
        $callback_data['status'] = 'success';
        $callback_data['third_order_no'] = 'SF' . time() . rand(1000, 9999);
        try {
            $client = new \GuzzleHttp\Client();
            $uri = $callback_data['notify_url'] . '?' . http_build_query($callback_data);
            $client->request('GET', $uri);
            return 'success';
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error($exception->getMessage());
            return 'fail';
        }
    }

}
