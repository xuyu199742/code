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
use Models\Activity\InnerOutsideGiveRecord;
use Models\Activity\PhonePayGive;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemSetting;
use Models\AdminPlatform\VipBusinessman;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;

class RechargeGive implements ShouldQueue
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
        $this->system = SystemSetting::where('group', 'recharge_percentage')->pluck('value', 'key')->toArray();
        if (!$this->system) {
            $this->system = system_configs('recharge_percentage');
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
        //查询赠送类型
        if (in_array($paymentOrder->payment_provider_id,PaymentOrder::OFFICIAL_KEYS)) {
            //内部充值订单
            $coins = $this->condition($paymentOrder, 'inner_'); //内部充值赠送
            $type_id = RecordTreasureSerial::INNER_RECHARGE_GIVE;
            $desc = '内部';
        } elseif($paymentOrder->payment_provider_id >0 || in_array($paymentOrder->payment_provider_id,PaymentOrder::CHANNEL) && $paymentOrder->payment_type != VipBusinessman::SIGN) {
            //外部充值订单
            $coins = $this->condition($paymentOrder, 'outside_');
            $type_id = RecordTreasureSerial::OUTSIDE_RECHARGE_GIVE;
            $desc = '外部';
        }else{
            return;
        }
        if ($coins > 0) {
            $local        = $score->Score;
            $score->Score += $coins;
            try {
                $paymentOrder::beginTransaction([$paymentOrder->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                //加流水
                RecordTreasureSerial::addRecord($paymentOrder->user_id, $local, $score->InsureScore, $coins, $type_id, $paymentOrder->admin_id);
                //给用户加金币
                $score->save();
                $data = [
                    'order_no' => $paymentOrder->order_no,
                    'user_id'  => $paymentOrder->user_id,
                    'coins'    => $coins,
                ];
                //充值赠送记录
                InnerOutsideGiveRecord::create($data);
                $paymentOrder::commit([$paymentOrder->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                //通知刷新金币
                $coins += $paymentOrder->coins;
                giveInform($paymentOrder->user_id, $score->Score, $coins);
                \Log::channel('queue')->info($desc.'充值赠送成功', $data);
                return;
            } catch (\Exception $e) {
                \Log::info($paymentOrder->user_id . $desc.'充值赠送加金币失败，事务回滚，应该加' . ($coins) . '金币,当前金币是' . $score->Score);
                $paymentOrder::rollBack([$paymentOrder->getConnectionName(), RecordTreasureSerial::connectionName(), GameScoreInfo::connectionName()]);
                \Log::channel('queue')->info($desc.'充值赠送失败：'.$e->getMessage(), $data);
                return;
            }
        }
        //通知刷新金币
        giveInform($paymentOrder->user_id, $score->Score, $paymentOrder->coins);
        return;

    }

    function condition($paymentOrder, $prefix)
    {
        if ($this->system[$prefix . 'is_open'] != 1) {
            return 0;
        }
        $percent_coins = $this->system[$prefix . 'recharge'] ?? 100;
        return $paymentOrder->coins * ($percent_coins - 100) / 100;
    }

}
