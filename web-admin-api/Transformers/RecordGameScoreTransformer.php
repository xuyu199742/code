<?php
/*用户进出记录*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
use Models\Treasure\RecordGameScore;
class RecordGameScoreTransformer extends  TransformerAbstract
{
    protected $availableIncludes = ['account','kind','server'];

    public function transform(RecordGameScore $item)
    {
        return  [
            'ID'                    => $item->ID,
            'UserID'                => $item->UserID,
            'ServerID'              => $item->ServerID,
            'ServerLevel'           => $item->ServerLevel,
            'KindID'                => $item->KindID,
            'ChangeScore'           => $item->ChangeScore,
            'JettonScore'           => $item->JettonScore,
            'SystemScore'           => $item->SystemScore,
            'SyetemServiceScore'    => $item->SyetemServiceScore,
            'UpdateTime'            => $item->UpdateTime,
            'CurrentScore'          => $item->CurrentScore,
        ];
    }

    /*关联用户信息*/
    public function includeAccount(RecordGameScore $item)
    {
        if(isset($item->account)){
            return $this->primitive($item->account,new AccountsInfoTransformer());
        }else{
            return $this->primitive(new AccountsInfo(),new AccountsInfoTransformer);
        }
    }

    /*关联游戏信息*/
    public function includeKind(RecordGameScore $item)
    {
        if(isset($item->kind)){
            return $this->primitive($item->kind,new GameKindItemTransformer);
        }else{
            return $this->primitive(new GameKindItem(),new GameKindItemTransformer);
        }
    }

    /*关联房间信息*/
    public function includeServer(RecordGameScore $item)
    {
        if(isset($item->server)){
            return $this->primitive($item->server,new GameRoomInfoTransformer);
        }else{
            return $this->primitive(new GameRoomInfo(),new GameRoomInfoTransformer);
        }
    }

}