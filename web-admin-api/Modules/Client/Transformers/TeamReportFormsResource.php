<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class TeamReportFormsResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id'          => $this->user_id,
            'start_date'       => $this->start_date,
            'person_score'     => realCoins($this->person_score),
            'team_score'       => realCoins($this->team_score),
            'reward_score'     => realCoins($this->reward_score),
            'directly_new'     => $this->directly_new ?? 0,
            'team_new'         => $this->team_new ?? 0,
        ];
    }
}
