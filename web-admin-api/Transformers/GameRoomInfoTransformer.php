<?php
/*游戏房间列表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Platform\GameRoomInfo;


class GameRoomInfoTransformer extends  TransformerAbstract
{
    public function transform(GameRoomInfo $item)
    {
        return [
            'ServerID'=>$item->ServerID,
            'ServerName'=>$item->ServerName,
            'KindID'=>$item->KindID,
//            'NodeID'=>$item->NodeID,
            'SortID'=>$item->SortID,
            'GameID'=>$item->GameID,
//            'TableCount'=>$item->TableCount,
//            'ServerKind'=>$item->ServerKind,
            'ServerType'=>$item->ServerType,
//            'ServerPort'=>$item->ServerPort,
//            'ServerLevel'=>$item->ServerLevel,
//            'ServerPasswd'=>$item->ServerPasswd,
//            'DataBaseName'=>$item->DataBaseName,
//            'DataBaseAddr'=>$item->DataBaseAddr,
//            'CellScore'=>$item->CellScore,
//            'RevenueRatio'=>$item->RevenueRatio,
//            'ServiceScore'=>$item->ServiceScore,
//            'RestrictScore'=>$item->RestrictScore,
//            'MinTableScore'=>$item->MinTableScore,
//            'MinEnterScore'=>$item->MinEnterScore,
//            'MaxEnterScore'=>$item->MaxEnterScore,
//            'MinEnterMember'=>$item->MinEnterMember,
//            'MaxEnterMember'=>$item->MaxEnterMember,
//            'MaxPlayer'=>$item->MaxPlayer,
//            'ServerRule'=>$item->ServerRule,
//            'DistributeRule'=>$item->DistributeRule,
//            'MinDistributeUser'=>$item->MinDistributeUser,
//            'DistributeTimeSpace'=>$item->DistributeTimeSpace,
//            'DistributeDrawCount'=>$item->DistributeDrawCount,
//            'MinPartakeGameUser'=>$item->MinPartakeGameUser,
//            'MaxPartakeGameUser'=>$item->MaxPartakeGameUser,
//            'AttachUserRight'=>$item->AttachUserRight,
//            'ServiceMachine'=>$item->ServiceMachine,
//            'CustomRule'=>$item->CustomRule,
//            'PersonalRule'=>$item->PersonalRule,
//            'Nullity'=>$item->Nullity,
//            'ServerNote'=>$item->ServerNote,
//            'CreateDateTime'=>$item->CreateDateTime,
//            'ModifyDateTime'=>$item->ModifyDateTime,
//            'EnterPassword'=>$item->EnterPassword,
        ];
    }
}