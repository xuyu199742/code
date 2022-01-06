<?php
/* 充值订单表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\PaymentOrder;
use Models\Agent\AccountInfo;


class PaymentOrderTransformer extends TransformerAbstract
{
    //充值订单基本信息
    public function transform(PaymentOrder $item)
    {
        //return $item->toArray();
        return [
            'id'                   => $item->id,
            'order_no'             => $item->order_no,
            'user_id'              => $item->user_id,
            'game_id'              => $item->game_id,
            'admin_id'             => $item->admin_id,
            'payment_provider_id'  => $item->payment_provider_id,
            'payment_type'         => $item->payment_type,
            'third_order_no'       => $item->third_order_no,
            'payment_status'       => $item->payment_status,
            'amount'               => $item->amount,
            'coins'                => $item->coins,
            'success_time'         => $item->success_time ? date('Y-m-d H:i:s', strtotime($item->success_time)) : '',
            'third_created_time'   => $item->third_created_time,
            'nickname'             => $item->nickname,
            'payment_provider_name'=> $item->payment_provider_name,
            'return_data'          => $item->return_data,
            'callback_data'        => $item->callback_data,
            'created_at'           => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
            'updated_at'           => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            'is_compensate'        => $item->payment_status != PaymentOrder::SUCCESS && !$item->relation_order_no ? 1 : 0,
            'remarks'              => $item->remarks,
            'relation_order_no'    => $item->relation_order_no
        ];

    }

}
