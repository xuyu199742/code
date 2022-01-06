<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Agent\AgentRelation;

class AgentBalanceReportDetailsTransformer extends TransformerAbstract
{

    public function transform(AgentRelation $item)
    {
        return [
            "user_id"           => $item->user_id,
            "game_id"           => $item->game_id,
            "sum_pay"           => $item->sum_pay ?? '0.00',//总充值
            "today_pay"         => $item->today_pay ?? '0.00',//当日充值
            "sum_withdrawal"    => $item->sum_withdrawal ?? '0.00',
            "today_withdrawal"  => $item->today_withdrawal ?? '0.00',
            "winlose"           => realCoins($item->winlose ?? 0),//玩家输赢
            "bet"               => realCoins($item->bet ?? 0),//投注
            "water"             => realCoins($item->water ?? 0),//流水
            "revenue"           => realCoins($item->revenue ?? 0),//税收
        ];
    }

}
