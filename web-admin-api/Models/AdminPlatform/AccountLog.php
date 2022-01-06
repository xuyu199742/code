<?php

namespace Models\AdminPlatform;


use Models\Agent\ChannelUserRelation;

class AccountLog extends Base
{
    protected $table      = 'account_log';
    public    $timestamps = false;
    const CLIENT_TYPE_ANDROID = 1;
    const CLIENT_TYPE_IOS     = 2;
    const CLIENT_TYPE_PC      = 3;
    const CLIENT_TYPE         = [
        self::CLIENT_TYPE_ANDROID => 'android',
        self::CLIENT_TYPE_IOS => 'ios',
        self::CLIENT_TYPE_PC => 'h5',
    ];

    const TYPE = [
        1   => '禁止登录',
        2   => '取消禁止登录',
        3   => '禁止提现',
        4   => '取消禁止提现',
        5   => '禁止登录及提现',
        6   => '取消禁止登录及提现',
    ];

    /*注册来源*/
    public function getClientTypeTextAttribute()
    {
        return self::CLIENT_TYPE[$this->client_type] ?? '';
    }
    /*操作类型*/
    public function getTypeTextAttribute()
    {
        return self::TYPE[$this->type] ?? '';
    }
    /*关联渠道*/
    public function channel()
    {
        return $this->hasOne(ChannelUserRelation::class,'user_id','user_id');
    }
}
