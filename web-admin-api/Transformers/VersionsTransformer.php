<?php
/*包管理*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\Version;


class VersionsTransformer extends TransformerAbstract
{

    public function transform(Version $item)
    {
        return [
            'id'                     => $item->id,
            'version_id'             => $item->version_id,
            'version_description'    => $item->version_description,
            'hot_update_url'         => $item->hot_update_url,
            'force_update_url'       => $item->force_update_url,
            'status'                 => $item->status,
            'status_text'            => $item->status_text,
        /*    'recharge_percent'       => $item->recharge_percent,
            'recharge_status'        => $item->recharge_status,
            'recharge_status_text'   => $item->recharge_status_text,
            'withdrawal_percent'     => $item->withdrawal_percent,
            'withdrawal_status'      => $item->withdrawal_status,
            'withdrawal_status_text' => $item->withdrawal_status_text,
            'min_withdrawal'         => $item->min_withdrawal,
            'withdrawal_desc'        => $item->withdrawal_desc,*/
        ];

    }

}
