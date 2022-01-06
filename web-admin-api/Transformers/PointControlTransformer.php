<?php
/*点控配置表*/

namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\PointControl;


class PointControlTransformer extends TransformerAbstract
{

    public function transform(PointControl $item)
    {
        if ($item->type == PointControl::FIXED_GOLD) { //固定金币
            $item->number = realCoins($item->number) ?? '0.00';
        }
        if (isset($item->finished_at)) {
            $item->finished_at = date('Y-m-d',strtotime($item->finished_at));
        }
        return [
            'id'          => $item->id,
            'game_id'     => $item->game_id,
            'type'        => $item->type==1 ?'固定金币':'固定局数',
            'target'      => $item->target,
            'number'      => $item->number,
            'process'     => $item->process,
            'probability' => bcadd(($item->probability/100),0,2),
            'priority'    => $item->priority,
            'winorlose'   => realCoins($item->winorlose) ?? '0.00',
            'status'      => $item->StatusText,
            'reason'      => $item->reason,
            'created_at'  => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',
            'finished_at' => $item->finished_at ?? '',
        ];
    }
}
