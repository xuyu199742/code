<?php
/*
 |--------------------------------------------------------------------------
 | 
 |--------------------------------------------------------------------------
 | Notes:
 | Class AdminUserTransformer
 | User: Administrator
 | Date: 2019/6/20
 | Time: 20:55
 | 
 |  * @return 
 |  |
 |
 */

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;


class AgentIncomeResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'person_score'         => realCoins($this->person_score),
            'team_score'           => realCoins($this->team_score),
            'reward_score'         => realCoins($this->reward_score),
            'sum_score'            => realCoins($this->team_score + $this->person_score),
            'created_at'           => date('m-d',strtotime($this->created_at)) ?? '',
        ];
    }

}