<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;

class ActivityReportDetailsTransformer extends TransformerAbstract
{

    public function transform(AccountsInfo $item)
    {
        return [
            "GameID"            => $item->GameID,
            "UserID"            => $item->UserID,
            "CollectDate"       => date('Y-m-d H:i:s',strtotime($item->CollectDate)) ?? '',
            "ChangeScore"       => realCoins($item->ChangeScore),
            'PlayTimeCount'     => $item->PlayTimeCount,
            "num"               => $item->num,
            "people_num"        => $item->people_num,
            "payment_score"     => $item->payment_score,
            "withdraw_score"    => $item->withdraw_score,
            "bet_score"         => realCoins($item->bet_score),
            "winlose_score"     => realCoins($item->winlose_score),
        ];
    }

}