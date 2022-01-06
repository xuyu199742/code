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

class UserManageTransformer extends TransformerAbstract
{
    public function transform(AccountsInfo $item)
    {
        return [
            'UserID'            => $item->UserID,
            'GameID'            => $item->GameID,
            'Accounts'          => $item->Accounts,
            'NickName'          => $item->NickName,
            'ClientType'        => $item->ClientType,
            'LogonMode'         => $item->LogonMode,
            'PlatformID'        => $item->PlatformID,
            'RegisterIP'        => $item->RegisterIP,
            'RegisterDate'      => date('Y-m-d H:i:s',strtotime($item->RegisterDate)) ?? '',
            'RegisterMobile'    => $item->RegisterMobile,
            'GameLogonTimes'    => $item->GameLogonTimes,
            'OnLineTimeCount'   => $item->OnLineTimeCount,
            'PlayTimeCount'     => $item->PlayTimeCount,
            'LastLogonIP'       => $item->LastLogonIP,
            'LastLogonDate'     => date('Y-m-d H:i:s',strtotime($item->LastLogonDate)) ?? '',
            'RegDeviceName'     => $item->RegDeviceName ?? '',
            'SystemVersion'     => $item->SystemVersion ?? '',
            'login_days'        => $item->login_days,
            //'nullity'           => $item->nullity,
            //'withdraw'          => $item->withdraw,
            //'JettonScore'       => realCoins($item->JettonScore),
            //'WinScore'          => realCoins($item->WinScore),
            //'bet'               => realCoins($item->bet),
            'user_win_lose'       => realCoins($item->win_lose), //玩家输赢=中奖-投注
            //'Score'             => $item->Score,
            //'channel_id'        => $item->channel_id,
            'pay_num'           => $item->pay_num,
            'pay'               => $item->pay_money??0,//总充值
            'withdrawal_num'    => $item->withdrawal_num ?? 0,
            'withdraw'          => $item->withdrawal_money ?? 0,
            'spring_water'      => realCoins($item->spring_water),//流水
            'active_money'      => realCoins($item->active_money),//活动礼金
            'parent_game_id'    => $item->parent_game_id,//父级游戏id
            'bind_channel'      => $item->bind_channel,//绑定渠道判断
            //重组格式
            'ClientTypeText'    => $item->ClientTypeText,
            'LogonModeText'     => $item->LogonModeText,
            'PlatformIDText'    => $item->PlatformIDText,
            'GameScoreInfo'     => [
                //'JettonScore'       => realCoins($item->JettonScore),//下注量
                //'WinScore'          => realCoins($item->WinScore),//输赢
                'Score'             => realCoins($item->Score),//余额
               // 'CurJettonScore'    => realCoins($item->CurJettonScore),//审核打码量
            ],
            'accountset'        => [
                'nullity'           => $item->nullity ?? 0,
                'withdraw'          => $item->withdraw ?? 0,
                'nullity_text'      => empty($item->nullity) ? '启用' : '禁用',
                'withdraw_text'     => empty($item->withdraw) ? '启用' : '禁用',
            ],
            'channel'           => [
                'channel_id'    => $item->channel_id ?? 0,
            ],
            //综合稽核打码量
            'AuditBetScore'     => realCoins($item->AuditBetScore) ?? 0,
            //筛选时间有效投注
            'bet'     => realCoins($item->bet) ?? 0,
            //总投注
            'sum_bet' => realCoins($item->sum_bet) ?? 0,
            'BankCardID'    => $item->BankCardID ?? '',
            'Compellation'  => $item->Compellation ?? '',
        ];
    }

}
