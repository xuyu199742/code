<?php
namespace Transformers;

use League\Fractal\TransformerAbstract;


class OuterPlatformGameRecordTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            "ID"                    => $item->ID,
            "PlatformID"            => $item->PlatformID,
            "KindID"                => $item->KindID,
            "ServerID"              => $item->ServerID,
            "ServerLevel"           => $item->ServerLevel,
            "UserID"                => $item->UserID,
            "GameID"                => $item->GameID,//游戏id
            "ChangeScore"           => realCoins($item->ChangeScore),//玩家盈利
            "JettonScore"           => realCoins($item->JettonScore),//有效投注
            "UpdateTime"            => date('Y-m-d H:i:s',strtotime($item->UpdateTime)),//拉取时间
            "OrderTime"            => date('Y-m-d H:i:s',strtotime($item->OrderTime)),//注单时间
            "OrderNo"               => $item->OrderNo,//注单号
            "PlatformName"          => $item->PlatformName ?? '',//平台名称
            "KindName"              => $item->KindName ?? '',//游戏名称
            "ServerName"            => $item->ServerName ? $item->ServerName : $item->room_name ?? '',

            "HaveOrderDetails"      => $item->have_order_details,
            "SystemScore"           => realCoins($item->SystemScore),
            "SystemServiceScore"    => realCoins($item->SystemServiceScore),
            "StreamScore"           => realCoins($item->StreamScore),
            "CurrentScore"          => realCoins($item->CurrentScore),
            "RewardScore"           => realCoins($item->RewardScore),
            "ValidJettonScore"      => realCoins($item->ValidJettonScore),
            "created_at"            => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',//遗漏注单的补单时间
        ];
    }

}
