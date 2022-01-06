<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Activity\ReturnRecord;

class ActivityReturnRecordTransformer extends TransformerAbstract
{

    public function transform(ReturnRecord $item)
    {
        return [
            "game_id"       => $item->GameID,
            "score"         => realCoins($item->score),//领取金额
            "win_score"     => realCoins($item->user_win_lose),//输赢改为：当前玩家输赢=中奖金额-投注金额
            "reward"        => realCoins($item->reward),//中奖金额
            "payout"        => realCoins( intval($item->bet) - intval($item->reward)),//派彩金额
            "reward_time"   => date('Y-m-d H:i:s',strtotime($item->reward_time)) ?? '',//领取时间
        ];
    }

}
