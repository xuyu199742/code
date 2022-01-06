<?php
//渠道和玩家关联表
namespace Models\Agent;


use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;

class ChannelUserRelation extends Base
{
    protected $table = 'channel_user_relation';
    public    $timestamps = false;


    public function gameScore()
    {
        return $this->hasOne(GameScoreInfo::class, 'UserID', 'user_id');
    }

    public function paymentOrderSumAmount()
    {
        return $this->hasMany(PaymentOrder::class, 'user_id', 'user_id');
    }

    public function withdrawalOrderSumAmount()
    {
        return $this->hasMany(WithdrawalOrder::class, 'user_id', 'user_id');
    }

    public function account()
    {
        return $this->hasOne(AccountsInfo::class, 'UserID', 'user_id');
    }

}
