<?php
/*锁定游戏用户*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
use Models\Treasure\GameChatUserInfo;

class GameChatUserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['account', 'kind', 'server'];

    public function transform(GameChatUserInfo $item)
    {
        return [
            'UserID'         => $item->UserID,
            'KindID'         => $item->KindID,
            'ServerID'       => $item->ServerID,
            'EnterID'        => $item->EnterID,
            'EnterIP'        => $item->EnterIP,
            'EnterMachine'   => $item->EnterMachine,
            'CollectDate'    => date('Y-m-d H:i:s', strtotime($item->CollectDate)) ?? '',
            'channel_sign'   => $item->channel->channel_id ?? '官方',
            'recharge_sum'   => $item->recharge_sum,
            'withdrawal_sum' => $item->withdrawal_sum,
            'score'          => $item->score,
        ];
    }

    /*关联用户信息*/
    public function includeAccount(GameChatUserInfo $item)
    {
        if (isset($item->account)) {
            return $this->primitive($item->account, new AccountsInfoTransformer);
        } else {
            return $this->primitive(new AccountsInfo(), new AccountsInfoTransformer);
        }

    }

    /*关联游戏信息*/
    public function includeKind(GameChatUserInfo $item)
    {
        if (isset($item->kind)) {
            return $this->primitive($item->kind, new GameKindItemTransformer);
        } else {
            return $this->primitive(new GameKindItem(), new GameKindItemTransformer);
        }
    }

    /*关联房间信息*/
    public function includeServer(GameChatUserInfo $item)
    {
        if (isset($item->server)) {
            return $this->primitive($item->server, new GameRoomInfoTransformer);
        } else {
            return $this->primitive(new GameRoomInfo(), new GameRoomInfoTransformer);
        }
    }

}
