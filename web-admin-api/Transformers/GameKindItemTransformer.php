<?php
/*游戏种类列表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;


class GameKindItemTransformer extends  TransformerAbstract
{
    protected $availableIncludes = ['rooms'];

    public function transform(GameKindItem $item)
    {
        return [
            'KindID'=> $item->KindID,
            'GameID'=> $item->GameID,
            'TypeID'=> $item->TypeID,
            //'JoinID'=> $item->JoinID,
            'SortID'=> $item->SortID,
            'KindName'=> $item->KindName,
            //'ProcessName'=> $item->ProcessName,
            //'GameRuleUrl'=> $item->GameRuleUrl,
            //'DownLoadUrl'=> $item->DownLoadUrl,
            //'Recommend'=> $item->Recommend,
            //'GameFlag'=> $item->GameFlag,
            //'Nullity'=> $item->Nullity,
        ];
    }

    /*关联房间信息*/
    public function includeRooms(GameKindItem $item)
    {
        if(isset($item->rooms)){
            return $this->collection($item->rooms,new GameRoomInfoTransformer());
        }else{
            return $this->primitive(new GameRoomInfo(),new GameRoomInfoTransformer);
        }
    }

}