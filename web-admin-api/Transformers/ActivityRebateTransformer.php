<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Activity\ActivityReturnConfig;

class ActivityRebateTransformer extends TransformerAbstract
{

    public function transform(ActivityReturnConfig $item)
    {
        if (isset($item->img_address)) {
            $item->all_img_address = cdn($item->img_address);
        }
        return [
            "id"                => $item->id,
            "activity_id"       => $item->activity_id,
            "name"              => $item->name,
            "category"          => $item->category,
            "category_text"     => $item->category_text,
            "nullity"           => $item->nullity,
            "nullity_text"      => $item->nullity_text,
            "activity_score"    => realCoins($item->activity_score),
            "img_address"       => $item->img_address,
            "all_img_address"   => $item->all_img_address,
            "start_time"        => date('Y-m-d H:i:s',strtotime($item->start_time)) ?? '',
            "end_time"          => date('Y-m-d H:i:s',strtotime($item->end_time)) ?? '',
            'status'            => $item->status,
        ];
    }

}
