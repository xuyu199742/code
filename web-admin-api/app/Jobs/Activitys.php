<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Models\Accounts\AccountsInfo;
use Models\Accounts\SystemStatusInfo;
use Models\Activity\ActivitiesNormal;
use Models\Activity\FirstChargeSignInLog;
use Models\Activity\InnerOutsideGiveRecord;
use Models\Activity\PhonePayGive;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemSetting;
use Models\AdminPlatform\VipBusinessman;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\UserAuditBetInfo;

class Activitys implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 2;

	public $timeout = 120;

	private $paymentOrder;
	private $gameScoreInfo;
	private $AuditBetScoreTake;
	private $beforeScore = 0;
	private $system;
	private $finalScore = 0;
	private $scoreFlow = [];

	/**
	 * Create a new job instance.
	 *
	 * @param $gameScoreInfo
	 * @param PaymentOrder $paymentOrder
	 * @param $beforeScore
	 *
	 * @return void
	 */
	public function __construct(GameScoreInfo $gameScoreInfo, PaymentOrder $paymentOrder, $beforeScore = 0)
	{
		$this->paymentOrder = $paymentOrder;
		$this->gameScoreInfo = $gameScoreInfo;
		$this->beforeScore = $beforeScore;
		//活动稽核打码倍数配置
		$AuditBetScoreTake = SystemStatusInfo::where('StatusName','AuditBetScoreTake')->value('StatusValue');
		$this->AuditBetScoreTake = $AuditBetScoreTake > 0 ? $AuditBetScoreTake: 100;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->handleFirstChargeSignIn();
		$this->handleFirstRecharge();
		$this->handleRechargeGive();
		$this->giveRefresh();
	}

	//首充签到活动记录
    private function handleFirstChargeSignIn()
    {
        \Log::channel('queue')->info('用户'.$this->paymentOrder->user_id.'首充签到活动开始');
        //第一次充值
        $p_count = PaymentOrder::where('user_id', $this->paymentOrder->user_id)->where('payment_status', PaymentOrder::SUCCESS)->count();
        if ($p_count != 1){
            \Log::channel('queue')->info('用户'.$this->paymentOrder->user_id.'不是第一次充值');
            return;
        }
        //同一设备仅可以参与一次
        $RegisterMachine = AccountsInfo::where('UserID', $this->paymentOrder->user_id)->value('RegisterMachine');
        $r_count = FirstChargeSignInLog::where('machine',$RegisterMachine)->count();
        if ($r_count > 0){
            \Log::channel('queue')->info('用户'.$this->paymentOrder->user_id.'同一设备仅可以参与一次');
            return;
        }
        //活动是否配置
        $ActivitiesNormal = ActivitiesNormal::where('id',1)->first();
        if (empty($ActivitiesNormal)){
            \Log::channel('queue')->info('首充签到活动未配置');
            return;
        }
        //活动是否开启
        if ($ActivitiesNormal->status != 1){
            \Log::channel('queue')->info('用户'.$this->paymentOrder->user_id.'活动未开启');
            return;
        }
        //活动最低充值金额是否满足
        $content    = json_decode($ActivitiesNormal->content,true);
        $min_money  = min(array_column($content['detail'],'money'));
        if ($this->paymentOrder->coins < $min_money){
            \Log::channel('queue')->info('用户'.$this->paymentOrder->user_id.'活动最低充值金额不满足');
            return;
        }
        //首充签到记录
        $FirstChargeSignInLog = new FirstChargeSignInLog();
        $FirstChargeSignInLog->user_id      = $this->paymentOrder->user_id;
        $FirstChargeSignInLog->score        = $this->paymentOrder->coins;
        $FirstChargeSignInLog->machine      = $RegisterMachine;
        $FirstChargeSignInLog->created_at   = date('Y-m-d H:i:s');
        $FirstChargeSignInLog->save();
        return;
    }


	private function handleFirstRecharge()
	{
		$this->system = SystemSetting::where('group', 'firstrecharge')->pluck('value', 'key')->toArray();
		//首充活动未开启
		if ($this->system['is_open'] == 0 && $this->system['mobile_is_open'] == 0) {
			return;
		}
		//首充活动未配置
		if (!$this->system) {
			$this->system = system_configs('firstrecharge');
			\Log::channel('queue')->info('未配置首充,用默认配置:', $this->system);
		}
		//是否第一次充值
		$p_count = PaymentOrder::where('user_id', $this->paymentOrder->user_id)->where('payment_status', PaymentOrder::SUCCESS)->count();
		$f_count = FirstRechargeLogs::where('order_no', $this->paymentOrder->order_no)->count();
		if ($p_count != 1 || $f_count) {
			//不符合活动条件
			return;
		}
		//查询玩家手机号
		$account = AccountsInfo::where('UserID', $this->paymentOrder->user_id)->first();
		//玩家手机号是否满足手机充值
		if (PhonePayGive::where('Phonenum', $account->RegisterMobile)->exists()) {
			$coins = $this->condition( 'mobile_');
			if (!$coins) { //当不满足手机首充条件，走全民首充
				$coins = $this->condition( '');
			}
		} else {
			//全民首充
			$coins = $this->condition( '');
		}
		if ($coins > 0) {
			//符合首充活动条件
			$this->scoreFlow[] = [
				$this->paymentOrder->user_id,
                $this->gameScoreInfo->Score+$this->finalScore,
				$this->gameScoreInfo->InsureScore,
				$coins,
				RecordTreasureSerial::FIRST_PAY_TYPE,
				$this->paymentOrder->admin_id,
                '',
                $this->paymentOrder->id,
                $coins * $this->AuditBetScoreTake / 100,
			];
			$this->finalScore += $coins;
			$data = [
				'order_no' => $this->paymentOrder->order_no,
				'user_id'  => $this->paymentOrder->user_id,
				'coins'    => $coins,
			];
			\Log::channel('queue')->info('活动赠送成功', $data);
			//首充记录
			FirstRechargeLogs::create($data);
		}
		return;
	}

	private function handleRechargeGive()
	{
        \Log::info("充值赠送：");
		//查询赠送类型
        if($this->paymentOrder->payment_provider_id == PaymentOrder::COMPENSATE_KEY){
            $old_payment_provider_id = PaymentOrder::where('order_no',$this->paymentOrder->relation_order_no)->value('payment_provider_id');
            $this->paymentOrder->payment_provider_id = $old_payment_provider_id;
        }
		if (in_array($this->paymentOrder->payment_provider_id, PaymentOrder::OFFICIAL_KEYS)) {
			//内部充值订单
			$coins = $this->RechargeGiveCondition('inner_'); //内部充值赠送
			$type_id = RecordTreasureSerial::INNER_RECHARGE_GIVE;
			$desc = '内部';
            \Log::info("内部：".$this->paymentOrder->payment_provider_id.',coins:'.$coins.',typeid:'.$type_id);
		} elseif ($this->paymentOrder->payment_provider_id > 0 || in_array($this->paymentOrder->payment_provider_id, PaymentOrder::CHANNEL) && $this->paymentOrder->payment_type != VipBusinessman::SIGN) {
			//外部充值订单
			$coins = $this->RechargeGiveCondition('outside_');
			$type_id = RecordTreasureSerial::OUTSIDE_RECHARGE_GIVE;
			$desc = '外部';
		}else {
			return;
		}
		$coins = round($coins); //保留整数
		if ($coins > 0) {
			//加流水
			$this->scoreFlow[] = [
				$this->paymentOrder->user_id,
				$this->gameScoreInfo->Score+$this->finalScore,
				$this->gameScoreInfo->InsureScore,
				$coins,
				$type_id,
				$this->paymentOrder->admin_id,
                '',
                $this->paymentOrder->id,
                $coins * $this->AuditBetScoreTake / 100
			];
			$this->finalScore += $coins;
			//给用户加金币
			$data = [
				'order_no' => $this->paymentOrder->order_no,
				'user_id'  => $this->paymentOrder->user_id,
				'coins'    => $coins,
			];
			//充值赠送记录
			InnerOutsideGiveRecord::create($data);
			//通知刷新金币
			\Log::channel('queue')->info($desc . '充值赠送成功', $data);
		}
		return;

	}

	private function condition($prefix)
	{
		if ($this->system[$prefix . 'is_open'] != 1) {
			return 0;
		}
		if ($this->system[$prefix . 'rebate_type'] == 1) {
			$lowest = $this->system[$prefix . 'fixed_lowest'] ?? 0;
			if ($this->paymentOrder->amount >= $lowest) {
				$coin = $this->system[$prefix . 'fixed_coins'] ?? 0;
				return $coin * realRatio();
			}
		}
		if ($this->system[$prefix . 'rebate_type'] == 2) {
			$lowest = $this->system[$prefix . 'percent_lowest'] ?? 0;
			$percent_coins = $this->system[$prefix . 'percent_coins'] ?? 0;
			if ($this->paymentOrder->amount >= $lowest) {
				return $this->paymentOrder->coins * $percent_coins / 100;
			}
		}
		return 0;
	}

	function RechargeGiveCondition($prefix)
	{
        //获取配置
        $system = SystemSetting::where('group', 'recharge_percentage')->pluck('value', 'key')->toArray();
        if (!$system) {
            $system = system_configs('recharge_percentage');
            \Log::channel('queue')->info('未配置首充,用默认配置:', $system);
        }
		if ($system[$prefix . 'is_open'] != 1) {
			return 0;
		}
		$percent_coins = $system[$prefix . 'recharge'] ?? 100;
		return $this->paymentOrder->coins * ($percent_coins - 100) / 100;
	}

	private function giveRefresh()
	{
		//给用户加金币
		try {
			$this->paymentOrder::beginTransaction([
				$this->paymentOrder->getConnectionName(),
				RecordTreasureSerial::connectionName(),
				GameScoreInfo::connectionName(),
                UserAuditBetInfo::connectionName()
			]);
			try{
                //加流水
                foreach ($this->scoreFlow as $ka => $array) {
                    $nextScore = $this->scoreFlow[$ka + 1][1] ?? ($this->gameScoreInfo->Score + $this->finalScore);
                    $activityName = RecordTreasureSerial::TYPEID[$array[4]] ?? '';
                    \Log::channel('gold_change')->info($this->paymentOrder->user_id . $activityName.'活动加金币之前,当前金币是'. $array[1]);
                    RecordTreasureSerial::addRecord(...$array);
                    \Log::channel('gold_change')->info($this->paymentOrder->user_id . $activityName.'活动加金币之后,当前金币是'. $nextScore);
                }
            }catch (\Exception $e){
                \Log::channel('gold_change')->info($this->paymentOrder->user_id .'活动充值错误：'.$e->getMessage(). ',当前金币是' . $this->gameScoreInfo->Score);
            }
			$this->gameScoreInfo->Score += $this->finalScore;
			$this->gameScoreInfo->save();
			//订单已加的金币 + (变化后的的金币 * 活动稽核倍数) - 稽核打码
            $addScore = bcadd($this->paymentOrder->coins,($this->finalScore * $this->AuditBetScoreTake / 100));
			$finalScore = bcadd($this->paymentOrder->coins,$this->finalScore);
			//稽核打码量
			\Log::info(config('set.auditBet').'量:'.$this->paymentOrder->user_id.'|'.$this->beforeScore.'|'.$addScore);
            UserAuditBetInfo::addScore($this->gameScoreInfo,$this->beforeScore,$addScore);
			$this->paymentOrder::commit([
				$this->paymentOrder->getConnectionName(),
				RecordTreasureSerial::connectionName(),
				GameScoreInfo::connectionName(),
                UserAuditBetInfo::connectionName()
			]);
			//通知刷新金币
			\Log::info('通知刷新金币:'.$this->paymentOrder->user_id.'|'.$this->gameScoreInfo->Score.'|'.$finalScore);
			giveInform($this->paymentOrder->user_id, $this->gameScoreInfo->Score,$finalScore);
			return;
		} catch (\Exception $e) {
			\Log::info($this->paymentOrder->user_id . '活动加金币失败，事务回滚，应该加' . ($this->finalScore) . '金币,当前金币是' . $this->beforeScore);
			$this->paymentOrder::rollBack([
				$this->paymentOrder->getConnectionName(),
				RecordTreasureSerial::connectionName(),
				GameScoreInfo::connectionName(),
                UserAuditBetInfo::connectionName()
			]);
			\Log::channel('queue')->info('活动赠送失败', $e->getMessage());
            \Log::channel('gold_change')->info($this->paymentOrder->user_id .'活动充值错误：'.$e->getMessage(). ',当前金币是' . $this->gameScoreInfo->Score);
			return;
		}

	}
}
