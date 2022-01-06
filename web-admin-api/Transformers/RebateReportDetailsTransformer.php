<?php
/*返利报表详情*/

namespace Transformers;

use League\Fractal\TransformerAbstract;

class RebateReportDetailsTransformer extends TransformerAbstract
{
    //返利报表详情分页
    public function transform($item)
    {
        return [
            'channel_id'            => $item -> channel_id,
            'user_id'               => $item -> user_id,
            'recharge_sum'          => $item -> recharge_sum,
            'recharge_today'        => $item -> recharge_today,
            'withdrawal_sum'        => $item -> withdrawal_sum,
            'withdrawal_today'      => $item -> withdrawal_today,
            'jetton_score'          => realCoins($item ->jetton_score),
            'stream_score'          => realCoins($item ->stream_score),
            'system_change_score'   => $item ->system_change_score,
            'system_service_score'  => realCoins($item ->system_service_score),
            'game_id'               => $item -> account -> GameID,
            'nickname'              => $item -> account -> NickName,
        ];
    }

}