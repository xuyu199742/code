<?php

namespace Models\AdminPlatform;


use App\Jobs\Activitys;
use App\Jobs\RechargeFirst;
use App\Jobs\RechargeGive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Models\Accounts\AccountsInfo;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Agent\ChannelInfo;
use Models\Treasure\UserAuditBetInfo;
use Ramsey\Uuid\Uuid;

class PaymentOrder extends Base
{
    const WAIT    = 'WAIT';
    const FAILS   = 'FAIL';
    const SUCCESS = 'SUCCESS';
    const STATUS  = [
        self::WAIT    => '等待到账',
        self::SUCCESS => '充值成功',
        self::FAILS   => '充值失败'
    ];
    //============官方充值常量==============
    const OFFICIAL_KEYS = [
        RechargeWechat::SIGN => 0,
        RechargeAlipay::SIGN => -1,
        RechargeUnion::SIGN  => -2,
        RechargeAgent::SIGN  => -3,
    ];
    const INNER_ORDER   = -4;
    const OFFICIAL      = [
        RechargeWechat::SIGN => '官方微信',
        RechargeAlipay::SIGN => '官方支付宝',
        RechargeUnion::SIGN  => '官方银联',
        RechargeAgent::SIGN  => '官方代理',
    ];
    //============官方充值常量==============

    //=============后续加入新的渠道需要在这添加===============
    const XIAOMI  = 'xiaomi';
    const PAY360  = '360';
    const CHANNEL = [
        self::XIAOMI => -5,
        self::PAY360 => -6
    ];
    //用于客户端参数
    const CHANNEL_NAME = [
        self::XIAOMI => '小米支付',
        self::PAY360 => '360支付'
    ];
    //补单类型
    const COMPENSATE_KEY = -7;
    const COMPENSATE = 'compensate';
    //===============渠道配置=============

    public function provider()
    {
        return $this->hasOne(PaymentProvider::class, 'id', 'payment_provider_id');
    }

    //生成三方订单号
    public static function producetOrderNo($s = 'M')
    {
        do {
            $no = $s.date('YmdHis').rand(10000,99999);
            // 为了避免重复我们在生成之后在数据库中查询看看是否已经存在相同的订单号
        } while (self::query()->where('order_no', $no)->exists());
        return $no;
    }

    //生产三方订单
    public function saveThirdOrder()
    {
        try {
            $provider                    = PaymentProvider::find(request('type'));
            $money                       = request('money');
            $this->payment_provider_id   = $provider->id;
            $this->payment_type          = $provider->pay_type;
            $this->payment_provider_name = $provider->provider_name;
            $user                        = AccountsInfo::where('UserID', request('user_id'))->first();
            $this->order_no              = self::producetOrderNo('T');
            $this->user_id               = $user->UserID;
            $this->game_id               = $user->GameID;
            $this->admin_id              = 0;
            $this->amount                = $money;
            $this->coins                 = $money * realRatio();
            $this->nickname              = $user->NickName;
            $this->payment_status        = self::WAIT;
            if (!$this->save()) {
                \Log::error('四方的订单生成失败');
                return false;
            }
            OrderLog::addLogs('玩家ID:' . $this->game_id . '充值:' . $this->amount . '金币,生成订单成功', $this->order_no, '四方充值订单');
            return true;
        } catch (\Exception $exception) {
            \Log::error('四方的订单生成异常' . $exception->getMessage());
            return false;
        }

    }

    //生产官方充值订单
    public function saveOfficialOrder()
    {
        // $provider                    = request('type');
        $money                       = request('money');
        $this->payment_provider_id   = self::OFFICIAL_KEYS[request('type')] ?? 0;
        $this->payment_type          = request('type');
        $this->payment_provider_name = self::OFFICIAL[request('type')] ?? '';
        $this->third_order_no        = request('third_order_no');
        $user                        = AccountsInfo::where('UserID', request('user_id'))->first();
        $this->order_no              = self::producetOrderNo('G');
        $this->user_id               = $user->UserID;
        $this->game_id               = $user->GameID;
        $this->admin_id              = 0;
        $this->amount                = $money;
        $this->coins                 = $money * realRatio();
        $this->nickname              = $user->NickName;
        $this->payment_status        = self::WAIT;
        if ($this->save()) {
            OrderLog::addLogs('玩家ID:' . $this->game_id . '充值:' . $this->amount . '金币,生成订单成功', $this->order_no, '官方充值订单');
            return true;
        }
        return false;
    }

    //生产渠道充值订单
    public function saveChannelOrder()
    {
        // $provider                    = request('type');
        $money = request('money');
        if (!isset(self::CHANNEL[request('type')])) {
            return false;
        }
        $this->payment_provider_id   = self::CHANNEL[request('type')];
        $this->payment_type          = request('type');
        $this->payment_provider_name = self::CHANNEL_NAME[request('type')] ?? '未知';
        $this->third_order_no        = request('third_order_no', '');
        $user                        = AccountsInfo::where('UserID', request('user_id'))->first();
        $this->order_no              = self::producetOrderNo('Q');
        $this->user_id               = $user->UserID;
        $this->game_id               = $user->GameID;
        $this->admin_id              = 0;
        $this->amount                = $money;
        $this->coins                 = $money * realRatio();
        $this->nickname              = $user->NickName;
        $this->payment_status        = self::WAIT;
        if ($this->save()) {
            OrderLog::addLogs('玩家ID:' . $this->game_id . '充值:' . $this->amount . '金币,生成订单成功', $this->order_no, self::CHANNEL_NAME[request('type')] . '充值订单');
            return true;
        }
        return false;
    }

    //生产内部赠送订单
    public function saveInnerOrder($money, $user_id)
    {
        // $provider                    = request('type');
        $this->payment_provider_id   = self::INNER_ORDER;
        $this->payment_type          = 'inner';
        $this->payment_provider_name = '内部赠送';
        $user                        = AccountsInfo::where('UserID', $user_id)->first();
        if (!$user) {
            return false;
        }
        $admin                = Auth::guard('admin')->user();
        $this->order_no       = self::producetOrderNo('N');
        $this->user_id        = $user->UserID;
        $this->game_id        = $user->GameID;
        $this->admin_id       = $admin->id;
        $this->amount         = $money;
        $this->coins          = moneyToCoins($money);
        $this->nickname       = $user->NickName;
        $this->payment_status = self::SUCCESS;
        $this->success_time   = Carbon::now();
        if ($this->save()) {
            OrderLog::addLogs('后台人员ID:' . $this->admin_id . '[' . $admin->username . ']' . '赠送给玩家ID:（' . $this->game_id . '）' . $this->amount . '金币', $this->order_no, '赠送订单');
            return true;
        }
        return false;
    }

    //生产vip商人订单
    public function saveVipOrder($money, $user_id, $vip_id)
    {
        $this->payment_provider_id   = $vip_id;
        $this->payment_type          = VipBusinessman::SIGN;
        $this->payment_provider_name = 'VIP商人';
        $user                        = AccountsInfo::where('UserID', $user_id)->first();
        if (!$user) {
            return false;
        }
        $admin                = Auth::guard('admin')->user();
        $this->order_no       = self::producetOrderNo('V');
        $this->user_id        = $user->UserID;
        $this->game_id        = $user->GameID;
        $this->admin_id       = $admin->id;
        $this->amount         = $money;
        $this->coins          = moneyToCoins($money);
        $this->nickname       = $user->NickName;
        $this->payment_status = self::WAIT;
        if ($this->save()) {
            OrderLog::addLogs('VIP商人为玩家ID:' . $this->game_id . '充值:' . $this->amount . '金币，生成了订单', $this->order_no, 'VIP充值订单');
            return true;
        }
        return false;
    }

    //生产补单订单
    public function saveCompensateOrder($order)
    {
        $admin = Auth::guard('admin')->user();
        try {
            $money                       = request('money');
            $this->payment_provider_id   = self::COMPENSATE_KEY;
            $this->payment_type          = self::COMPENSATE;
            $this->payment_provider_name = '补单订单';
            $this->order_no              = self::producetOrderNo('B');
            $this->user_id               = $order->user_id;
            $this->game_id               = $order->game_id;
            $this->admin_id              = $admin->id;
            $this->amount                = $money;
            $this->coins                 = $money * realRatio();
            $this->nickname              = $order->nickname;
            $this->payment_status        = self::WAIT;
            $this->relation_order_no     = $order->order_no;
            $this->remarks               = request('remarks');
            if (!$this->save()) {
                \Log::error('补单的订单生成失败');
                return false;
            }
            OrderLog::addLogs('玩家ID:' . $this->game_id . '补偿:' . $this->amount . '金币,生成订单成功', $this->order_no, '补单订单');
            return true;
        } catch (\Exception $exception) {
            \Log::error('补单的订单生成异常' . $exception->getMessage());
            return false;
        }

    }

    //官方添加金币
    public function officialAddCoins()
    {
        $score = GameScoreInfo::where('UserID', $this->user_id)->first();
        if (!$score) {
            Log::info($this->user_id . '用户不存在。无法加金币');
            return false;
        }
        $local                = $score->Score;
        $score->Score         += $this->coins; //给用户加金币
        $this->success_time   = Carbon::now();
        $this->admin_id       = \Auth::id() ?? 0;
        $this->payment_status = self::SUCCESS;
        \Log::channel('gold_change')->info($this->user_id .'官方充值加金币之前,当前金币是' . $local);
        try {
            self::beginTransaction([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            if (RecordTreasureSerial::addRecord($this->user_id, $local, $score->InsureScore, $this->coins, RecordTreasureSerial::INNER_PAY_TYPE, $this->admin_id,'',$this->id,$this->coins)
                && $this->save() && $score->save()) {
                OrderLog::addLogs('后台人员ID:' . $this->admin_id . ',操作订单:' . $this->order_no . '添加了' . $this->amount . '【' . $this->coins . '】金币', $this->order_no, '官方充值订单');
                self::commit([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                \Log::channel('gold_change')->info($this->user_id .'官方充值加金币之后,当前金币是' . $score->Score);
				//充值活动
                $this->activities($score,$local);
                //渠道充值返利结算
                $this->channelRechargeRebate();
                return true;
            } else {
                \Log::channel('gold_change')->info($this->user_id .'官方充值加金币错误,当前金币是' . $local);
                \Log::info($this->user_id . '加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' . $local);
                self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                return false;
            }
        } catch (\Exception $e) {
            \Log::channel('gold_change')->info($this->user_id .'官方充值加金币错误：'.$e->getMessage().',当前金币是' . $local);
            \Log::error($this->user_id . '加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' .$local);
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            return false;
        }
    }

    //四方添加金币
    public function thirdAddCoins()
    {
        $score = GameScoreInfo::where('UserID', $this->user_id)->first();
        if (!$score) {
            Log::info($this->user_id . '用户不存在。无法加金币');
            return false;
        }
        self::beginTransaction([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
        $order = self::where('order_no', $this->order_no)->lockForUpdate()->first(); //查询订单
        if ($order->payment_status == PaymentOrder::SUCCESS){
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            return false;
        }

        $local                = $score->Score;
        $score->Score         += $this->coins; //给用户加金币
        $this->success_time   = Carbon::now();
        $this->admin_id       = \Auth::id() ?? 0;
        $this->payment_status = self::SUCCESS;
        \Log::channel('gold_change')->info($this->user_id .'四方充值加金币之前,当前金币是' . $local);
        try {
            if (RecordTreasureSerial::addRecord($this->user_id, $local, $score->InsureScore, $this->coins, RecordTreasureSerial::PAY_TYPE, $this->admin_id,'',$this->id,$this->coins)
                && $this->save() && $score->save()) {
                OrderLog::addLogs('系统为订单:' . $this->order_no . '添加了' . $this->amount . '【' . $this->coins . '】金币', $this->order_no, '三方充值订单');
                self::commit([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                \Log::channel('gold_change')->info($this->user_id .'四方充值加金币之后,当前金币是' . $score->Score);
	            //充值活动
	            $this->activities($score,$local);
                //渠道充值返利结算
                $this->channelRechargeRebate();
                return true;
            } else {
                \Log::channel('gold_change')->info($this->user_id .'四方充值加金币错误,当前金币是' . $local);
                \Log::info($this->user_id . '加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' . $local);
                self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                return false;
            }
        } catch (\Exception $e) {
            \Log::channel('gold_change')->info($this->user_id .'四方充值加金币错误：'.$e->getMessage().',当前金币是' . $local);
            \Log::error($this->user_id . '加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' . $local);
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            return false;
        }
    }

    private function activities($score,$beforeScore){
        try{
            \Log::channel('gold_change')->info($this->user_id . '活动充值之前,当前金币是'. $score->Score);
            \Log::info("开始活动记录：");
            Activitys::dispatch($score,$this,$beforeScore)->onQueue('high');
            \Log::info("活动记录结束。");
        }catch (\Exception $e){
            \Log::channel('gold_change')->info($this->user_id .'活动充值错误：'.$e->getMessage(). ',当前金币是' . $score->Score);
        }
        $vip_upgrade_type = config('set.vip_upgrade_type') ?? 2;
        if($vip_upgrade_type == 2){     //1：充值 2：有效投注
            return;
        }else{
            try{
                //vip加经验
                AccountsInfo::addExp($this->user_id,$this->amount);
            }catch (\Exception $e){
                \Log::error('vip加经验错误：'.$e->getMessage());
            }
            return;
        }
    }

    //vip商人添加金币
    public function vipAddCoins()
    {
        $score = GameScoreInfo::where('UserID', $this->user_id)->first();
        if (!$score) {
            Log::info($this->user_id . '用户不存在。无法加金币');
            return false;
        }
        $local                = $score->Score;
        $score->Score         += $this->coins; //给用户加金币
        $this->success_time   = Carbon::now();
        $this->admin_id       = \Auth::id() ?? 0;
        $this->payment_status = self::SUCCESS;
        \Log::channel('gold_change')->info($this->user_id .'vip商人添加金币之前,当前金币是' . $local);
        //处理vip商人余额
        $business = VipBusinessman::find($this->payment_provider_id);
        if (!$business) {
            Log::info('VIP商人id:' . $business->id . '不存在');
            return false;
        }
        if ($business->gold_coins < 0 || $business->gold_coins < $this->coins) {
            Log::info('VIP商人id:' . $business->id . '的余额不足');
            return false;
        }
        $business->gold_coins -= $this->coins;
        try {
            self::beginTransaction([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName(), VipBusinessman::connectionName()]);
            if (RecordTreasureSerial::addRecord($this->user_id, $local, $score->InsureScore, $this->coins, RecordTreasureSerial::VIP_BUSINESS, $this->admin_id,'',$this->id,$this->coins)
                && $business->save() && $this->save() && $score->save()) {
                OrderLog::addLogs('VIP商人id:' . $business->id . ',后台人员ID:' . $this->admin_id . ',该订单:' . $this->order_no . '添加了' . $this->amount . '【' . $this->coins . '】金币', $this->order_no, 'VIP充值订单');
                self::commit([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName(), VipBusinessman::connectionName()]);
                \Log::channel('gold_change')->info($this->user_id .'vip商人添加金币之前,当前金币是' . $score->Score);
	            //充值活动
	            $this->activities($score,$local);
                //渠道充值返利结算
                $this->channelRechargeRebate();
                return true;
            } else {
                \Log::channel('gold_change')->info($this->user_id .'vip商人添加金币错误,当前金币是' . $local);
                \Log::info('VIP商人充值:' . $this->user_id . '加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' . $local);
                self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName(), VipBusinessman::connectionName()]);
                return false;
            }
        } catch (\Exception $e) {
            \Log::channel('gold_change')->info($this->user_id .'vip商人添加金币错误：'.$e->getMessage().',当前金币是' . $local);
            \Log::error('VIP商人充值:' . $this->user_id . '加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' . $local);
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName(), VipBusinessman::connectionName()]);
            return false;
        }
    }

    //渠道充值返利
    private function channelRechargeRebate()
    {
        try {
            //渠道充值返利
            ChannelInfo::RechargeRebate($this);
        } catch (\Exception $e) {
        }
    }

    //渠道商添加金币
    public function channelAddCoins()
    {
        $score = GameScoreInfo::where('UserID', $this->user_id)->first();
        if (!$score) {
            Log::info($this->user_id . '用户不存在。无法加金币');
            return false;
        }
        $local                = $score->Score;
        $score->Score         += $this->coins; //给用户加金币
        $this->success_time   = Carbon::now();
        $this->admin_id       = \Auth::id() ?? 0;
        $this->payment_status = self::SUCCESS;
        \Log::channel('gold_change')->info($this->user_id .'渠道商添加金币之前,当前金币是' . $local);
        try {
            self::beginTransaction([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            if (RecordTreasureSerial::addRecord($this->user_id, $local, $score->InsureScore, $this->coins, RecordTreasureSerial::CHANNEL_PAY, $this->admin_id,'',$this->order_no,$this->coins)
                && $this->save() && $score->save()) {
                OrderLog::addLogs('系统为订单:' . $this->order_no . '添加了' . $this->amount . '【' . $this->coins . '】金币', $this->order_no, '渠道充值订单');
                self::commit([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                \Log::channel('gold_change')->info($this->user_id .'渠道商添加金币之前,当前金币是' . $score->Score);
                try {
                    //通知刷新金币
                    giveInform($this->user_id, $score->Score, $this->coins);
                    //渠道充值返利
                    ChannelInfo::RechargeRebate($this);
                } catch (\Exception $e) {
                    return true;
                }
                return true;
            } else {
                \Log::channel('gold_change')->info($this->user_id .'充渠道商添加金币错误,当前金币是' . $local);
                \Log::info($this->user_id . '渠道商添加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' . $score->Score);
                self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                return false;
            }
        } catch (\Exception $e) {
            \Log::channel('gold_change')->info($this->user_id .'充渠道商添加金币错误：'.$e->getMessage().',当前金币是' . $local);
            \Log::error($this->user_id . '加金币失败，事务回滚，应该加' . ($this->coins) . '【' . $this->amount . '】' . '金币,当前金币是' . $score->Score);
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            return false;
        }
    }

    public function orderFails()
    {
        $this->success_time   = Carbon::now();
        $this->admin_id       = \Auth::id() ?? 0;
        $this->payment_status = self::FAILS;
        if ($this->save()) {
            OrderLog::addLogs('管理员ID为:' . $this->admin_id . '的管理员,将订单:' . $this->order_no . '的设置为失败状态', $this->order_no, '官方充值订单');
            return true;
        }
        return false;
    }

    public function getStatusTextAttribute()
    {
        return isset(self::STATUS[$this->payment_status]) ? self::STATUS[$this->payment_status] : '';
    }

}
