<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\ManuallyFailedException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Models\Accounts\AccountsInfo;
use Models\Activity\PhonePayGive;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemSetting;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;

class RechargeFirst implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public $timeout = 120;

    protected $paymentOrder;

    private $system;

    /**
     * Create a new job instance.
     *
     * @param PaymentOrder $paymentOrder
     *
     * @return void
     */
    public function __construct(PaymentOrder $paymentOrder)
    {
        $this->paymentOrder = $paymentOrder;
        //获取配置
        $this->system = SystemSetting::where('group', 'firstrecharge')->pluck('value', 'key')->toArray();
        if (!$this->system) {
            $this->system = system_configs('firstrecharge');
            \Log::channel('queue')->info('未配置首充,用默认配置:', $this->system);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $paymentOrder = $this->paymentOrder;
        $score        = GameScoreInfo::where('UserID', $paymentOrder->user_id)->first();
        if (!$score) {
            return;
        }
        //是否第一次充值
        $p_count = PaymentOrder::where('user_id', $paymentOrder->user_id)->where('payment_status', $paymentOrder::SUCCESS)->count();
        $f_count = FirstRechargeLogs::where('order_no', $paymentOrder->order_no)->count();
        if ($p_count != 1 || $f_count) {
            //通知刷新金币
            giveInform($paymentOrder->user_id, $score->Score, $paymentOrder->coins);
	        AccountsInfo::addExp($paymentOrder->user_id,$paymentOrder->amount);
            return;
        }
        //查询玩家手机号
        $account = AccountsInfo::where('UserID', $paymentOrder->user_id)->first();
        //玩家手机号是否满足手机充值
        if (PhonePayGive::where('Phonenum', $account->RegisterMobile)->exists()) {
            $coins = $this->condition($paymentOrder, 'mobile_');
            if (!$coins) { //当不满足手机首充条件，走全民首充
                $coins = $this->condition($paymentOrder, '');
            }
        } else {
            //全民首充
            $coins = $this->condition($paymentOrder, '');
        }
        if ($coins > 0) {
            $local        = $score->Score;
            $score->Score += $coins;
            try {
                $paymentOrder::beginTransaction([$paymentOrder->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                //加流水
                RecordTreasureSerial::addRecord($paymentOrder->user_id, $local, $score->InsureScore, $coins, RecordTreasureSerial::FIRST_PAY_TYPE, $paymentOrder->admin_id);
                //给用户加金币
                $score->save();
                $data = [
                    'order_no' => $paymentOrder->order_no,
                    'user_id'  => $paymentOrder->user_id,
                    'coins'    => $coins,
                ];
                \Log::channel('queue')->info('首充赠送成功', $data);
                //首充记录
                FirstRechargeLogs::create($data);
                $paymentOrder::commit([$paymentOrder->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                //通知刷新金币
                $coins += $paymentOrder->coins;
                giveInform($paymentOrder->user_id, $score->Score, $coins);
	            AccountsInfo::addExp($paymentOrder->user_id,$paymentOrder->amount);
                return;
            } catch (\Exception $e) {
                \Log::info($paymentOrder->user_id . '首充加金币失败，事务回滚，应该加' . ($coins) . '金币,当前金币是' . $score->Score);
                $paymentOrder::rollBack([$paymentOrder->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                \Log::channel('queue')->info('首充赠送失败', $data);
                return;
            }
        }
        //通知刷新金币
        giveInform($paymentOrder->user_id, $score->Score, $paymentOrder->coins);
	    AccountsInfo::addExp($paymentOrder->user_id,$paymentOrder->amount);
        return;

    }

    function condition($paymentOrder, $prefix)
    {
        if ($this->system[$prefix . 'is_open'] != 1) {
            return 0;
        }
        if ($this->system[$prefix . 'rebate_type'] == 1) {
            $lowest = $this->system[$prefix . 'fixed_lowest'] ?? 0;
            if ($paymentOrder->amount >= $lowest) {
                $coin = $this->system[$prefix . 'fixed_coins'] ?? 0;
                return $coin * realRatio();
            }
        }
        if ($this->system[$prefix . 'rebate_type'] == 2) {
            $lowest        = $this->system[$prefix . 'percent_lowest'] ?? 0;
            $percent_coins = $this->system[$prefix . 'percent_coins'] ?? 0;
            if ($paymentOrder->amount >= $lowest) {
                return $paymentOrder->coins * $percent_coins / 100;
            }
        }
        return 0;
    }


}
