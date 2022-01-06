<?php
namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;

class WashCodeRecordTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            'id'                    => $item->id,
            'game_id'               => AccountsInfo::find($item->user_id)->GameID ?? '',
            'aggregate_wash_code'   => realCoins($item->aggregate_wash_code),
            'jetton_score'          => realCoins($item->jetton_score),
            'wash_code'             => realCoins($item->wash_code),
            'jetton_total_score'    => realCoins($item->jetton_total_score),
            'created_at'            => date('Y-m-d H:i:s',strtotime($item->created_at)),
        ];

    }


}
