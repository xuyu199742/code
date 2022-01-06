<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class AgentBalanceReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
            "user_id"       => $item->user_id,
            "game_id"       => $item->GameID,
            "reward_score"  => realCoins($item->reward_score),
            "person_score"  => realCoins($item->person_score),
            "agent_score"  => realCoins($item->agent_score),
            "created_at"    => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',
        ];
    }

}
