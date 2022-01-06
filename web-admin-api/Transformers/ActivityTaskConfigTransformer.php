<?php
/*活动列表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Activity\ActivityTaskConfig;

class ActivityTaskConfigTransformer extends TransformerAbstract
{
    //活动列表
    public function transform(ActivityTaskConfig $item)
    {
        return [
            'id'                    => $item -> id,
            'activity_id'           => $item -> activity_id,
            'kind_id'               => $item -> kind_id,
            'server_id'             => $item -> server_id,
            'game_name'             => $item -> KindName,
            'room_name'             => $item -> ServerName,
            'category'              => $item -> category,
            'category_text'         => $item -> category_text,
            'condition'             => $item -> condition,
            'reward'                => realCoins($item -> reward),
            'receive_reward_times'  => $item -> reward_record->times,
            'receive_reward_scores' => realCoins($item -> reward_record->score) ?? 0,
            'is_cycle'              => $item -> is_cycle,
            'cycle_day'             => $item -> cycle_day,
            'task_num'              => $item -> task_num,
            'nullity'               => $item -> nullity,
            'created_at'            => date('Y-m-d H:i:s', strtotime($item->created_at)),
            'updated_at'            => date('Y-m-d H:i:s', strtotime($item->updated_at))
        ];
    }
}