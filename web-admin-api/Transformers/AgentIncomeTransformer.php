<?php
/*代理返利结算记录表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Agent\AgentIncome;
class AgentIncomeTransformer extends TransformerAbstract
{

    public function transform(AgentIncome $item)
    {
        return [
            'id'                =>      $item->id,
            'user_id'           =>      $item->user_id,
            'person_score'      =>      realCoins($item->person_score),
            'team_score'        =>      realCoins($item->team_score),
            'reward_score'      =>      realCoins($item->reward_score),
            'start_date'        =>      $item->start_date,
            'end_date'          =>      $item->end_date,
            'created_at'        =>      $item->created_at,
        ];
    }

}