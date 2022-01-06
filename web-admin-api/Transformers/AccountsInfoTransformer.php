<?php
/*用户信息表*/
namespace Transformers;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AccountsSet;
use Models\Agent\AgentRelation;
use Models\Agent\ChannelUserRelation;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;

class AccountsInfoTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['accountset','channel','GameScoreInfo','agent'];
    //用户表基本信息
    public function transform(AccountsInfo $item)
    {
        return [
            'UserID'            => $item->UserID,
            'GameID'            => $item->GameID,
            'Accounts'          => $item->Accounts,
            'NickName'          => $item->NickName,
            'PassPortID'        => $item->PassPortID,
            'Compellation'      => $item->Compellation,
            'IsAndroid'         => $item->IsAndroid,
            'GameLogonTimes'    => $item->GameLogonTimes,
            'PlayTimeCount'     => $item->PlayTimeCount,
            'OnLineTimeCount'   => $item->OnLineTimeCount,
            'LastLogonIP'       => $item->LastLogonIP,
            'LastLogonDate'     => date('Y-m-d H:i:s',strtotime($item->LastLogonDate)) ?? '',
            'LastLogonMobile'   => $item->LastLogonMobile,
            'RegisterIP'        => $item->RegisterIP,
            'RegisterDate'      => date('Y-m-d H:i:s',strtotime($item->RegisterDate)) ?? '',
            'RegisterMobile'    => $item->RegisterMobile,
            'ClientType'        => $item->ClientType,
            'PlatformID'        => $item->PlatformID,
            'LogonMode'         => $item->LogonMode,

            'ClientTypeText'    => $item->ClientTypeText,
            'LogonModeText'     => $item->LogonModeText,
            'PlatformIDText'    => $item->PlatformIDText,

            'pay'               => realCoins($item->pay),//总充值
            'withdraw'          => realCoins($item->withdraw),
            'spring_water'      => realCoins($item->spring_water),//总流水
            'user_win_lose'     => realCoins($item->user_win_lose),//玩家输赢
            'parent_game_id'    => $item->parent_game_id,//父级游戏id
            'bind_channel'      => $item->bind_channel,

        ];

    }

    /*关联用户信息*/
    public function includeAccountset(AccountsInfo $item)
    {
        if(isset($item->accountset)){
            return $this->primitive($item->accountset,new AccountsSetTransformer);
        }else{
            return $this->primitive(new AccountsSet(),new AccountsSetTransformer);
        }
    }

    /*关联代理中间表信息*/
    public function includeAgent(AccountsInfo $item)
    {
        if(isset($item->agent)){
            return $this->primitive($item->agent,new AgentRelationTransformer);
        }else{
            return $this->primitive(new AgentRelation(),new AgentRelationTransformer);
        }
    }

    /*关联用户金币信息*/
    public function includeGameScoreInfo(AccountsInfo $item)
    {
        if(isset($item->GameScoreInfo)){
            return $this->primitive($item->GameScoreInfo,new GameScoreInfoTransformer);
        }else{
            return $this->primitive(new GameScoreInfo(),new GameScoreInfoTransformer);
        }
    }

    /*关联代理中间表信息*/
    public function includeChannel(AccountsInfo $item)
    {
        if(isset($item->channel)){
            return $this->primitive($item->channel,new ChannelUserRelationTransformer);
        }else{
            return $this->primitive(new ChannelUserRelation(),new ChannelUserRelationTransformer);
        }
    }

    /*关联用户登录表信息*/
    public function includeUserLogin(AccountsInfo $item)
    {
        if(isset($item->userLogin)){
            return $this->primitive($item->userLogin,new RecordUserLogonTransformer());
        }else{
            return $this->primitive(new RecordUserLogon(),new RecordUserLogonTransformer);
        }
    }

}
