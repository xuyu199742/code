<?php
/*用户进出记录*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
use Models\Treasure\RecordUserInout;


class RecordUserInoutTransformer extends  TransformerAbstract
{
    protected $availableIncludes = ['account','kind','server'];

    /*用户进出记录主表信息*/
    public function transform(RecordUserInout $item)
    {
        return [
            'ID' => $item->ID,
            'UserID' => $item->UserID,
            'KindID' => $item->KindID,
            'ServerID' => $item->ServerID,
            'EnterTime' => date('Y-m-d H:i:s',strtotime($item->EnterTime)) ?? '',
            'EnterScore' => realCoins($item->EnterScore ?? 0),
            //'EnterGrade' => realCoins($item->EnterGrade ?? 0),
            'EnterInsure' => realCoins($item->EnterInsure ?? 0),
            'EnterMachine' => $item->EnterMachine,
            'EnterClientIP' => $item->EnterClientIP,
            'LeaveTime' => date('Y-m-d H:i:s',strtotime($item->LeaveTime)) ?? '',
            'LeaveReason' => $item->LeaveReason,
            'LeaveMachine' => $item->LeaveMachine,
            'LeaveClientIP' => $item->LeaveClientIP,
            'Score' => realCoins($item->Score ?? 0),
            //'Grade' => realCoins($item->Grade ?? 0),
            'Insure' => realCoins($item->Insure ?? 0),
            'Revenue' => realCoins($item->Revenue ?? 0),
            'WinCount' => $item->WinCount,
            'LostCount' => $item->LostCount,
            'DrawCount' => $item->DrawCount,
            'FleeCount' => $item->FleeCount,
            'PlayTimeCount' => $item->PlayTimeCount,
            'OnLineTimeCount' => $item->OnLineTimeCount,
        ];
    }

    /*关联用户信息*/
    public function includeAccount(RecordUserInout $item)
    {
        if(isset($item->account)){
            return $this->primitive($item->account,new AccountsInfoTransformer);
        }else{
            return $this->primitive(new AccountsInfo(),new AccountsInfoTransformer);
        }
    }

    /*关联游戏信息*/
    public function includeKind(RecordUserInout $item)
    {
        if(isset($item->kind)){
            return $this->primitive($item->kind,new GameKindItemTransformer);
        }else{
            return $this->primitive(new GameKindItem(),new GameKindItemTransformer);
        }

    }

    /*关联房间信息*/
    public function includeServer(RecordUserInout $item)
    {
        if(isset($item->server)){
            return $this->primitive($item->server,new GameRoomInfoTransformer);
        }else{
            return $this->primitive(new GameRoomInfo(),new GameRoomInfoTransformer);
        }
    }

}