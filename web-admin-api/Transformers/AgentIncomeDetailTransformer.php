<?php
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Agent\AgentIncomeDetails;

class AgentIncomeDetailTransformer extends TransformerAbstract
{

    public function transform(AgentIncomeDetails $item)
    {
        return [
            'user_id' => $item->user_id,
            'start_date' => $item->start_date,
            'person_score' => realCoins($item->person_score ?? 0),
            'team_score' => realCoins($item->team_score ?? 0),
            'reward_score' => realCoins($item->reward_score ?? 0),
            'directly_new' => $item->directly_new ?? 0,
            'team_new' => $item->team_new ?? 0
        ];
    }
}
