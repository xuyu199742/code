<?php

namespace Modules\Client\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AgentRedPacketRelation;
use Models\AdminPlatform\OrderLog;
use Models\AdminPlatform\PaymentOrder;
use Models\Agent\AgentRelation;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\UserAuditBetInfo;
use Models\Accounts\SystemStatusInfo;

class AgentRedPacketController extends Controller
{
	/**
	 * @return \Illuminate\Config\Repository|\Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|mixed
	 * @throws \Illuminate\Validation\ValidationException
	 *  代理红包列表
	 */
	public function list()
	{
		Validator::make(request()->all(), [
			'user_id' => ['required', 'numeric'],
		], [
			'user_id.required' => '用户user_id必传!',
			'user_id.numeric'  => '用户user_id必须是数字，请重新输入！',
		])->validate();
		$user = AccountsInfo::with('agent')->where('UserID', request('user_id'))->first();

		if ($user) {
			$red_packets = AgentRedPacketRelation::where('user_id', $user->UserID)->pluck('sign'); //获取领取记录
			$sub = AgentRelation::from(AgentRelation::tableName() . ' as a')
				->select(\DB::raw('sum(b.amount) as amounts'))
				->leftJoin(PaymentOrder::tableName() . ' as b', 'b.user_id', '=', 'a.user_id')
				->where('b.payment_status', PaymentOrder::SUCCESS)
				->where('a.parent_user_id', $user->UserID)
				->groupBy('a.user_id')
				->havingRaw('sum(b.amount) >= 100');
			$son_agent_count = \DB::table(\DB::raw("({$sub->toSql()}) as sub"))->mergeBindings($sub->getQuery())
				->count();
			$arr = config('agent_red_packet');
			//判断是否领取了红包
			foreach ($arr as $key => $value) {
				if (in_array($key, $red_packets->toArray())) {
					$arr[$key]['has_get'] = true;
				} else {
					$arr[$key]['has_get'] = false;
				}
				if ($son_agent_count >= $value['people']) {
					$arr[$key]['match'] = true;
				}
			}
			return ResponeSuccess('获取成功', $arr);
		}
		return ResponeFails('用户不存在');
	}

	public function getRedPacket()
	{
		$packets = config('agent_red_packet');
		Validator::make(request()->all(), [
			'user_id'    => ['required', 'numeric'],
			'packet_num' => ['required', Rule::in(array_keys($packets))],
		], [
			'user_id.required' => '用户user_id必传!',
			'user_id.numeric'  => '用户user_id必须是数字，请重新输入！',
			'packet_num.in'    => '超出红包领取范围',
		])->validate();
		$user = AccountsInfo::with('agent')->where('UserID', request('user_id'))->first();
		$red_packet_num = \request('packet_num');
		if (!isset($packets[$red_packet_num])) {
			return ResponeFails('该红包不存在');
		}

		if ($user) {
			$red_packets = AgentRedPacketRelation::where('user_id', $user->UserID)->pluck('sign'); //获取领取记录
			//判断红包是否领取
			if (in_array($red_packet_num, $red_packets->toArray())) {
				return ResponeFails('该红包已经领取，请不要重复领取');
			}
			$get_red_packet = $packets[$red_packet_num];
			$sub = AgentRelation::from(AgentRelation::tableName() . ' as a')
				->select(\DB::raw('sum(b.amount) as amounts'))
				->leftJoin(PaymentOrder::tableName() . ' as b', 'b.user_id', '=', 'a.user_id')
				->where('b.payment_status', PaymentOrder::SUCCESS)
				->where('a.parent_user_id', $user->UserID)
				->groupBy('a.user_id')
				->havingRaw('sum(b.amount) >= 100');
			$son_agent_count = \DB::table(\DB::raw("({$sub->toSql()}) as sub"))->mergeBindings($sub->getQuery())
				->count();
			if ($son_agent_count < $get_red_packet['people']) {
				return ResponeFails('还差一点就可以领取了，加油');
			}
			//处理领取红包
			$money = 0;
			if ($get_red_packet['random'] && is_array($get_red_packet['range'])) {
				$money = random_int($get_red_packet['range'][0], $get_red_packet['range'][1]);
				$money = $money - ($money % 10) + 8;
			} else {
				$money = $get_red_packet['range'];
			}
			$coin = $money * realRatio();
			//加金币，加流水
			$score = GameScoreInfo::where('UserID', $user->UserID)->first();
			if (!$score) {
				return ResponeFails('用户不存在');
			}
            $local = $score->Score;
			try {
				GameScoreInfo::beginTransaction([
					GameScoreInfo::connectionName(),
					RecordTreasureSerial::connectionName(),
					AgentRedPacketRelation::connectionName(),
                    UserAuditBetInfo::connectionName()
				]);
                // 增加代理红包稽核
				$AuditBetScoreTake = SystemStatusInfo::where('StatusName','AuditBetScoreTake')->value('StatusValue');
                $audit_bet = intval($coin * $AuditBetScoreTake / 100);

				$score->Score += $coin;
				$record = RecordTreasureSerial::addRecordReturnID($user->UserID, $local, $score->InsureScore, $coin, RecordTreasureSerial::AGENT_RED_PACKET,0,$audit_bet);
				$relation = AgentRedPacketRelation::addRecord($red_packet_num, $money, $user->UserID, $coin, $get_red_packet, $record);

				// 增加代理红包稽核
                $UserAuditBetInfo = UserAuditBetInfo::where('UserID', $user->UserID)->first();
                if($UserAuditBetInfo){
                    $UserAuditBetInfo->AuditBetScore = $UserAuditBetInfo->AuditBetScore + $audit_bet;
                }else{
                    $UserAuditBetInfo = new UserAuditBetInfo();
                    $UserAuditBetInfo->AuditBetScore = $audit_bet;
                }
                $UserAuditBetInfo -> UserID = $user->UserID;
                $UserAuditBetInfo->save();

				if ($relation && $record && $score->save()) {
					GameScoreInfo::commit([GameScoreInfo::connectionName(), RecordTreasureSerial::connectionName(),AgentRedPacketRelation::connectionName(),UserAuditBetInfo::connectionName()]);
					//金币通知
					giveInform($user->UserID, $score->Score, $coin);
					return ResponeSuccess('领取成功');
				}
				GameScoreInfo::rollBack([GameScoreInfo::connectionName(), RecordTreasureSerial::connectionName(),AgentRedPacketRelation::connectionName(),UserAuditBetInfo::connectionName()]);
				return ResponeFails('系统异常');
			} catch (\Exception $exception) {
				\Log::info($user->UserID . '代理红包加金币失败，事务回滚，应该加【' . $coin . '】' . '金币,当前金币是' . $local);
				GameScoreInfo::rollBack([GameScoreInfo::connectionName(), RecordTreasureSerial::connectionName(),AgentRedPacketRelation::connectionName()],UserAuditBetInfo::connectionName());
				return ResponeFails('系统异常');
			}
		}
		return ResponeFails('用户不存在');
	}

}
