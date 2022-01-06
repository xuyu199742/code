<?php
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Agent\AgentIncome;

class AgentBalanceReportDetails extends TransformerAbstract
{

    public function transform(AgentIncome $item)
    {
        return [
            'user_id' => $item->user_id,
            'start_date' => $item->start_date,
            'person_score' => realCoins($item->person_score ?? 0),
            'team_score' => realCoins(($item->team_score + $item->person_score) ?? 0),
            'reward_score' => realCoins($item->reward_score ?? 0),
            'directly_new' => $item->directly_new ?? 0,
            'team_new' => $item->team_new ?? 0
        ];
    }

}
