<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\MembersHandsel;
use Models\Accounts\MembersHandselLogs;


class MembersHandselLogsTransformer extends  TransformerAbstract
{

    public function transform(MembersHandselLogs $item)
    {
        $data = $item->toArray();
        $data['WinScore']    = realCoins($data['UserWinLose']);//当前输赢改为：当前玩家输赢=中奖-投注
        $data['RewardScore'] = realCoins($data['RewardScore']);
        $data['PayOutScore'] = realCoins($data['PayOutScore']);
        $data['JettonScore'] = realCoins($data['JettonScore']);//当前有效投注
        $data['HandselName'] = MembersHandsel::getTypeName($item);
        $data['HandselCoins']= realCoins($data['HandselCoins']);
        $data['CreatedTime'] = date('Y-m-d H:i:s',strtotime($data['CreatedTime']));
        $data['VipUpgradeTime'] = date('Y-m-d H:i:s',strtotime($data['VipUpgradeTime']));
        return $data;
    }

}
