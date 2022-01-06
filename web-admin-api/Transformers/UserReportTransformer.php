<?php
/*用户代理关联表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;

class UserReportTransformer extends TransformerAbstract
{

    public function transform(AccountsInfo $item)
    {
        return [
            "UserID"            => $item->UserID,
            "GameID"            => $item->GameID,
            "RegisterDate"      => date('Y-m-d H:i:s',strtotime($item->RegisterDate)) ?? '',
            "Score"             => realCoins($item->Score),//当前剩余金币
            "InsureScore"       => realCoins($item->InsureScore ?? 0),//银行存款
            "JettonScore"       => realCoins($item->jetton_score ?? 0),//有效投注
            "bet"               => realCoins($item->bet ?? 0),//投注
            "bet_num"           => $item->bet_num,//投注次数
            "pay_score"         => $item->pay_score ?? '0.00',//充值
            "pay_num"           => $item->pay_num,//充值次数
            "withdrawal_score"  => $item->withdrawal_score ?? '0.00',
            "withdrawal_num"    => $item->withdrawal_num,//次数
            "winlose"           => realCoins($item->winlose ?? 0),//输赢
            "water"             => realCoins($item->water ?? 0),//流水
            "active_score"      => realCoins($item->active_score ?? 0),//活动
            "active_num"        => $item->active_num,//活动次数
            "game_days"         => $item->game_days,//游戏天数
            "parent_game_id"    => !empty($item->parent_game_id) ? $item->parent_game_id : '',//上级代理game_id
            "channel_id"        => $item->channel_id ?? '',//渠道id
            //"PayoutScore"       => realCoins($item->PayoutScore ?? 0),//派奖
            "WinnerPaid"        => realCoins($item->WinnerPaid ?? 0),//中奖
            //"profit"            => realCoins($item->profit ?? 0),//赢利
            "coins"             => realCoins($item->coins ?? 0),//首充
            "vip_level"         => $item->vip_level,//用户级别
        ];
    }

}
