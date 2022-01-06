<?php
/*用户信息表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AccountInfo;


class WithdrawalOrderTransformer extends TransformerAbstract
{
    //用户表基本信息
    public function transform(WithdrawalOrder $item)
    {
        return [
            'id'              => $item->id,
            'seo_id'          => $item->seo_id,
            'user_id'         => $item->user_id,
            'game_id'         => $item->game_id,
            'admin_id'        => $item->admin_id,
            'username'        => $item->admin->username ?? '',
            'order_no'        => $item->order_no,
            'card_no'         => $item->card_no,
            'bank_info'       => $item->bank_info,
            'payee'           => $item->payee,
            'phone'           => $item->phone,
            'gold_coins'      => $item->gold_coins,
            'real_gold_coins' => $item->coins,
            'status'          => $item->status,
            'status_text'     => isset( $item->withdrawalAuto->subset_status_text)?$item->withdrawalAuto->subset_status_text:$item->status_text,
            'money'           => $item->money,
            'real_money'      => $item->real_money,
            'client_ip'       => $item->client_ip,
            'payment_no'      => $item->payment_no,
            'complete_time'   => $item->complete_time ? date('Y-m-d H:i:s', strtotime($item->complete_time)) : '',
            'remark'          => $item->remark,
            'created_at'      => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
            'updated_at'      => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            'withdrawal_type' => $item->withdrawal_type_text,
            'jetton_score'    => isset($item->jetton_score) ? realCoins($item->jetton_score) : 0,
            'withdrawal_auto'     =>  [
                'order_id'          => $item->withdrawalAuto->order_id??'',
                'third_order_no'    => $item->withdrawalAuto->third_order_no??'',
                'withdrawal_status' => $item->withdrawalAuto->withdrawal_status ?? '',
                'lock_id'           => $item->withdrawalAuto->lock_id ?? '',
                'subset_status_text'=> $item->withdrawalAuto->subset_status_text??'',
            ],
        ];
    }

}
