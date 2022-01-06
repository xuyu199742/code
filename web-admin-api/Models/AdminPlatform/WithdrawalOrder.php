<?php

namespace Models\AdminPlatform;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Models\Accounts\AccountsInfo;
use Models\Agent\AgentUserRelation;
use Models\Agent\ChannelInfo;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Ramsey\Uuid\Uuid;

class WithdrawalOrder extends Base
{
    //public $dates=['complete_time'];
    //protected $dateFormat = 'Y-m-d H:i:s';
    public $guarded = ['seo_id', 'admin_id', 'money', 'real_money', 'order_no', 'status'];
    protected $primaryKey = 'id';

    const PAY_FAILS = -2;
    const CHECK_FAILS = -1;
    const WAIT_PROCESS = 0;
    const CHECK_PASSED = 1;
    const PAY_SUCCESS = 2;

    const STATUS = [
        self::PAY_FAILS => '汇款失败',
        self::CHECK_FAILS => '审核失败',
        self::WAIT_PROCESS => '等待审核',
        self::CHECK_PASSED => '财务待审核',
        self::PAY_SUCCESS => '汇款到账'
    ];
    const FINANCE_STATUS = [
        self::PAY_FAILS => '汇款失败',
        self::CHECK_PASSED => '财务待审核',
        self::PAY_SUCCESS => '汇款到账'
    ];
    const WITHDRAWAL_TYPE = 1; //vip商人



    public static function subStatus()
    {
        return [
            ['value' => self::PAY_FAILS, 'label' => self::FINANCE_STATUS[self::PAY_FAILS], 'children' => [
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::AUTOMATIC_FAILS,'label'=>WithdrawalAutomatic::PAYMENT_FAILS[WithdrawalAutomatic::AUTOMATIC_FAILS]],
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::ARTIFICIAL_FAILS,'label'=>WithdrawalAutomatic::PAYMENT_FAILS[WithdrawalAutomatic::ARTIFICIAL_FAILS]],
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::ARTIFICIAL_FAILS_REFUSE,'label'=>WithdrawalAutomatic::PAYMENT_FAILS[WithdrawalAutomatic::ARTIFICIAL_FAILS_REFUSE]],
            ]],
            ['value' => self::CHECK_PASSED, 'label' => self::FINANCE_STATUS[self::CHECK_PASSED], 'children' => [
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::FINANCE_CHECK,'label'=>WithdrawalAutomatic::FINANCE_WAIT[WithdrawalAutomatic::FINANCE_CHECK]],
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::AUTOMATIC_PAYMENT,'label'=>WithdrawalAutomatic::FINANCE_WAIT[WithdrawalAutomatic::AUTOMATIC_PAYMENT]],
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::LOCK,'label'=>WithdrawalAutomatic::FINANCE_WAIT[WithdrawalAutomatic::LOCK]]
            ]],
            ['value' => self::PAY_SUCCESS, 'label' => self::FINANCE_STATUS[self::PAY_SUCCESS], 'children' => [
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::AUTOMATIC_SUCCESS,'label'=>WithdrawalAutomatic::PAYMENT_SUCCESS[WithdrawalAutomatic::AUTOMATIC_SUCCESS]],
                ['value'=>WithdrawalAutomatic::ALIAS_PERFIX.WithdrawalAutomatic::ARTIFICIAL_SUCCESS,'label'=>WithdrawalAutomatic::PAYMENT_SUCCESS[WithdrawalAutomatic::ARTIFICIAL_SUCCESS]],
            ]]
        ];
    }

    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }

    /*订单关联*/
    public function withdrawalAuto()
    {
        return $this->belongsTo(WithdrawalAutomatic::class, 'id', 'order_id');
    }

    /**
     * 初始化生成订单
     *
     * @return bool
     */
    public function initOrder()
    {
        self::beginTransaction([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
        $score = GameScoreInfo::where('UserID', $this->user_id)->lockForUpdate()->first();
        //$seo   = AgentUserRelation::where('user_id', $this->user_id)->first();
        if ($score) {
            $account_info = AccountsInfo::where('UserID', $this->user_id)->first();
            if (!$account_info) {
                return false;
            }
            \Log::channel('gold_change')->info($this->user_id . '金币'.config('set.withdrawal').'之前,当前金币是'. $score->Score);
            try {
                //初始化订单数据
                $this->game_id = $account_info->GameID;
                //$this->seo_id          = $seo->agent_channel_id ?? 0;
                $this->order_no = self::getOrderNo();
                $this->real_gold_coins = $this->gold_coins * realRatio();
                $this->money = coinsToMoney($this->gold_coins);
                $this->client_ip = Request::getClientIp();
                $this->jetton_score = $score->CurJettonScore ?? 0;
                //添加金币流水记录
                $record = RecordTreasureSerial::addRecord(
                    $this->user_id,
                    $score->Score,
                    $score->InsureScore,
                    -((int)$this->real_gold_coins),
                    RecordTreasureSerial::WITHDRAWAL_TYPE,
                    '',
                    $this->id
                );
                //扣除用户金币
                $score->Score -= $this->real_gold_coins;
                //防止并发扣除金币不足，为负数
                if ($score->Score < 0){
                    \Log::channel('gold_change')->info($this->user_id . config('set.withdrawal').'金币不足,当前金币是'. $score->Score);
                    self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                    return false;
                }
                if ($record && $this->save() && $score->save()) {
                    \Log::channel('gold_change')->info($this->user_id . config('set.withdrawal').'成功,当前金币是'. $score->Score);
                    self::commit([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                    return true;
                }
                \Log::channel('gold_change')->info($this->user_id . config('set.withdrawal').'失败,当前金币是'. $score->Score);
                self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            } catch (\Exception $e) {
                self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                \Log::channel('gold_change')->info($this->user_id . config('set.withdrawal').'失败,当前金币是'. $score->Score);
                \Log::error(config('set.withdrawal').'表单保存失败: ' . $e->getMessage(), $this->attributes);
            }
        }
        return false;
    }

    public function rollBackCoins()
    {
        $this->complete_time = date('Y-m-d H:i:s');
        if ($this->status == self::WAIT_PROCESS) {
            return $this->backCoins(self::CHECK_FAILS);
        }

        if ($this->status == self::CHECK_PASSED) {
            return $this->backCoins(self::PAY_FAILS);
        }

        return false;
    }

    private function backCoins($status)
    {
        if ($this->withdrawal_type == self::WITHDRAWAL_TYPE) {
            //属于vip商人，返还金币单独处理
            return $this->vipBackCoins($status);
        }
        $score = GameScoreInfo::where('UserID', $this->user_id)->first();
        if (!$score) {
            Log::error('玩家已经不存在,用户id' . $this->user_id . '游戏id:' . $this->game_id);
            return false;
        }
        $this->status = $status;
        $localcoin = $score->Score;
        $score->Score += $this->real_gold_coins; //返还用户金币
        $this->admin_id = Auth::guard('admin')->id();
        $record_treasure_serial = new RecordTreasureSerial();
        try {
            self::beginTransaction([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            $record = $record_treasure_serial->addRecord($this->user_id, $localcoin, $score->InsureScore, (int)$this->real_gold_coins, RecordTreasureSerial::WITHDRAWAL_BACK, $this->admin_id,'',$this->id);
            if ($record && $this->save() && $score->save()) {
                self::commit([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                try {
                    //通知刷新金币
                    giveInform($this->user_id, $score->Score, $this->real_gold_coins);
                } catch (\Exception $e) {
                    return true;
                }
                return true;
            }
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            return false;
        } catch (\Exception $e) {
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            Log::error('金币回滚失败:' . $e->getMessage(), $this->attributes);
        }
        return false;
    }

    //vip返还金币
    private function vipBackCoins($status)
    {
        $score = GameScoreInfo::where('UserID', $this->user_id)->first();
        if (!$score) {
            Log::error('玩家已经不存在,用户id' . $this->user_id . '游戏id:' . $this->game_id);
            return false;
        }
        $this->status = $status;
        $localcoin = $score->InsureScore;
        $score->InsureScore += $this->real_gold_coins; //返还用户金币
        $this->admin_id = Auth::guard('admin')->id();
        $record_treasure_serial = new RecordTreasureSerial();

        $vip = GameScoreInfo::where('UserID', $this->payment_no)->first();
        if (!$vip) {
            Log::error('vip不存在,用户id' . $this->payment_no);
            return false;
        }
        $vip_localcion = $vip->InsureScore;
        $vip->InsureScore -= $this->real_gold_coins; //扣除vip金币
        try {
            self::beginTransaction([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            $record = $record_treasure_serial->addRecord($this->user_id, $score->Score, $localcoin, (int)$this->real_gold_coins, RecordTreasureSerial::WITHDRAWAL_BACK, $this->admin_id,'',$this->id);
            $vip_record = $record_treasure_serial->addRecord($this->payment_no, $vip->Score, $vip_localcion, -(int)$this->real_gold_coins, RecordTreasureSerial::VIP_WITHDRAWAL_BACK, $this->admin_id,'',$this->id);
            if ($record && $vip_record && $this->save() && $score->save() && $vip->save()) {
                self::commit([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                try {
                    //通知刷新金币
                    giveInform($this->user_id, $score->Score, $this->real_gold_coins);
                } catch (\Exception $e) {
                    return true;
                }
                return true;
            }
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            return false;
        } catch (\Exception $e) {
            self::rollBack([$this->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
            Log::error('金币回滚失败:' . $e->getMessage(), $this->attributes);
        }
        return false;
    }

    private static function getOrderNo()
    {
        do {
            // Uuid类可以用来生成大概率不重复的字符串
            $no = Uuid::uuid4()->getHex();
            // 为了避免重复我们在生成之后在数据库中查询看看是否已经存在相同的订单号
        } while (self::query()->where('order_no', $no)->exists());
        return $no;
    }

    public function getStatusTextAttribute()
    {
        return isset(self::STATUS[$this->status]) ? self::STATUS[$this->status] : '';
    }

    public function getCoinsAttribute()
    {
        return realCoins($this->real_gold_coins);
    }

    public function getWithdrawalTypeTextAttribute()
    {
        return $this->withdrawal_type == 1 ? 'VIP'.config('set.withdrawal') : '兑换'.config('set.withdrawal');
    }
}
