<?php
/*用户信息表*/

namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Agent\ChannelWithdrawRecord;


class ChannelWithdrawRecordTransformer extends TransformerAbstract
{
    //用户表基本信息
    public function transform(ChannelWithdrawRecord $item)
    {
        return [
            'id'            => $item -> id,
            'channel_id'    => $item -> channel_id,
            'order_no'      => $item -> order_no,
            'value'         => $item -> value,
            'payee'         => $item -> payee,
            'phone'         => $item -> phone,
            'bank_info'     => $item -> bank_info,
            'card_no'       => $item -> card_no,
            'status'        => $item -> status,
            'admin_id'        => $item -> admin_id,
            'status_text'   => $item -> status_text,
            'created_at'    => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',
            'updated_at'    => date('Y-m-d H:i:s',strtotime($item->updated_at)) ?? '',
            'username'      => $item->admin->username ?? '',
            'remark'        => $item->remark ?? '',
        ];
    }

}
