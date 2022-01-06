<?php
/*任务活动领取列表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Activity\TaskRecord;

class TaskRecordTransformer extends TransformerAbstract
{
	//任务活动领取列表
	public function transform(TaskRecord $item)
	{
		return [
			'game_id'     => $item->game_data->GameID ?? '/',
			'reward_time' => $item->reward_time ? date('Y-m-d H:i:s', strtotime($item->reward_time)) : '/',
			'times'       => $item->receive_record->times ?? '',
			'score'       => realCoins($item->receive_record->score ?? 0), //领取彩金
			'win_score'   => realCoins($item->reward_score->user_win_lose ?? 0),  //当前玩家输赢
			'reward'      => realCoins($item->reward_score->score ?? 0),          //当前中奖
			'payout'      => realCoins($item->reward_score->payout ?? 0),         //当前派彩

		];
	}

}
