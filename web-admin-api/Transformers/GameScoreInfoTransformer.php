<?php
/*用户进出记录*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Treasure\GameScoreInfo;


class GameScoreInfoTransformer extends  TransformerAbstract
{
    protected $availableIncludes = ['account','kind','server'];

    public function transform(GameScoreInfo $item)
    {
        return [
            'UserID'            =>      $item->UserID,
            'Score'             =>      realCoins($item->Score),
            'Revenue'           =>      realCoins($item->Revenue),
            'InsureScore'       =>      realCoins($item->InsureScore),
            'JettonScore'       =>      realCoins($item->JettonScore),
            'WinCount'          =>      $item->WinCount,
            'LostCount'         =>      $item->LostCount,
            'DrawCount'         =>      $item->DrawCount,
            'FleeCount'         =>      $item->FleeCount,
            'UserRight'         =>      $item->UserRight,
            'MasterTight'       =>      $item->MasterTight,
            'MasterOrder'       =>      $item->MasterOrder,
            'AllLogonTimes'     =>      $item->AllLogonTimes,
            'PlayTimeCount'     =>      $item->PlayTimeCount ?? 0,
            'OnlineTimeCount'   =>      $item->OnlineTimeCount ?? 0,
            'LastLogonIP'       =>      $item->LastLogonIP,
            'LastLogonDate'     =>      date('Y-m-d H:i:s',strtotime($item->LastLogonDate)),
            'LastLogonMachine'  =>      $item->LastLogonMachine,
            'RegisterIP'        =>      $item->RegisterIP,
            'RegisterDate'      =>      date('Y-m-d H:i:s',strtotime($item->RegisterDate)),
            'RegisterMachine'   =>      $item->RegisterMachine,
            'WinScore'          =>      realCoins($item->WinScore),//输赢
        ];
    }

    /*关联用户信息*/
    public function includeAccount(GameScoreInfo $item)
    {
        if(isset($item->account)){
            return $this->primitive($item->account,new AccountsInfoTransformer);
        }else{
            return $this->primitive(new AccountsInfo(),new AccountsInfoTransformer);
        }

    }

}