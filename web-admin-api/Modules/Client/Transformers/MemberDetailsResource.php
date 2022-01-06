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


class MemberDetailsResource extends Resource
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
            'game_id'                     => $this->game_id,
            'nickname'                    => $this->nickname,
            //'recharge_num'                => $this->recharge_num,
            //'withdrawal_num'              => $this->withdrawal_num,
            //'cash_gifts'                  => $this->cash_gifts,
            //'recharge_give'               => $this->recharge_give,
            'today_jetton_score'          => $this->today_jetton_score,
            'jetton_score'                => $this->jetton_score,
            'team_num'                    => $this->team_num,
            'directly_player_num'         => $this->directly_player_num,
        ];
    }

}
