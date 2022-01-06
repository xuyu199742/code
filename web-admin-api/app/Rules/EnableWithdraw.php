<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\GameScoreLocker;
use Models\Treasure\UserAuditBetInfo;

class EnableWithdraw implements Rule
{
	private $user_id;


	public $message;

	/**
	 * EnableDeposit constructor.
	 *
	 * @param $user_id
	 */
	public function __construct($user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		if (!system_configs('coin.withdrawal_status')) {
			$this->message = config('set.withdrawal').'渠道关闭';
			return false;
		}

		//判断用户是否锁定
		if (GameScoreLocker::where('UserID', $this->user_id)->exists()) {
			$this->message = '还在游戏中,无法'.config('set.withdrawal');
			return false;
		}
		//获取用户金币和当日打码量
		$game_score_info = GameScoreInfo::where('UserID', $this->user_id)->first(['Score', 'CurJettonScore']);
		if (!$game_score_info) {
			$this->message = '无法'.config('set.withdrawal');
			return false;
		}

		//转换为游戏服务器的金币值
		$coins = $value * realRatio();

		//判断用户金币是否满足条件
		if ($game_score_info->Score < $coins) {
			$this->message = '金币不足';
			return false;
		}
		//计算是否是10的倍数
		if ($value % 10 != 0) {
			$this->message = config('set.withdrawal').'金币必须是10的倍数';
			return false;
		}

		//最少判断
		if (getMinWithdrawal() > $coins) {
			$this->message = '没有达到最低'.config('set.withdrawal').'额';
			return false;
		}
		//存在未处理的订单，不能操作
		$audit_num = WithdrawalOrder::where('user_id', $this->user_id)->where(function ($query) {
			$query->where('status', WithdrawalOrder::WAIT_PROCESS)
				->orWhere(function ($query) {
					$query->where('status', WithdrawalOrder::CHECK_PASSED);
				});
		})->count();
		if ($audit_num > 0) {
			$this->message = '您有'.config('set.withdrawal').'订单在审核';
			return false;
		}

		//每人每天最多5次（成功）
		/*$withdarwal_times = WithdrawalOrder::where('user_id',$this->user_id)->whereDate('created_at',date('Y-m-d',time()))->where('status',WithdrawalOrder::PAY_SUCCESS)->count();
		if ($withdarwal_times > 4){
			$this->message = '每日最多只能操作5次';
			return false;
		}*/


		/* if ($game_score_info->CurJettonScore < $coins){
			 $this->message = '当日打码量不足';
			 return false;
		 }*/
//		$orders = WithdrawalOrder::where('user_id', $this->user_id)
//			->where('status', WithdrawalOrder::PAY_SUCCESS)
//			->orderBy('created_at', 'desc')
//			->first();
//		$start_time = '';
//		$end_time = date('Y-m-d H:i:s');
//		if (isset($orders)) {
//			$start_time = $orders->created_at;
//		}
//		$amount = PaymentOrder::andFilterWhere('created_at', '<=', $end_time)
//			->andFilterWhere('created_at', '>=', $start_time)
//			->where('user_id', $this->user_id)
//			->where('payment_status', PaymentOrder::SUCCESS)
//			->sum('amount');
//        $bet_multiple = system_configs('withdraw_bet.bet_multiple');
//        if(!$bet_multiple || $bet_multiple < 1){
//            $this->message = '打码量倍数不能小于1';
//            return false;
//        }
//		if (bcmul($game_score_info->CurJettonScore,$bet_multiple,2)  < $amount*realRatio()) {
//        if ($game_score_info->CurJettonScore  < $amount*realRatio()) {
//			$this->message = '未达到条件,如有疑问请咨询客服';
//			return false;
//		}
        $AuditBet = UserAuditBetInfo::where('UserID',$this->user_id)->first();
        if($AuditBet){
            if($AuditBet->AuditBetScore > 0){
                //$this->message = '打码量不足，您还差'.realCoins($AuditBet->AuditBetScore);
                $this->message = '打码量不足，无法'.config('set.withdrawal');
                return false;
            }
        }
		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return $this->message;
	}
}
