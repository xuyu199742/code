<?php

namespace Models\AdminPlatform;


class AccountsSet extends Base
{
    const NULLITY_ON   = 0;
    const NULLITY_OFF  = 1;
    const NULLITY      = [
        self::NULLITY_ON  => '启用',
        self::NULLITY_OFF => '禁用',
    ];
    const WITHDRAW_ON  = 0;
    const WITHDRAW_OFF = 1;
    const WITHDRAW     = [
        self::WITHDRAW_ON  => '启用',
        self::WITHDRAW_OFF => '禁用',
    ];


    protected $table      = 'accounts_set';
    public    $timestamps = false;
    public    $fillable   = ['user_id', 'nullity', 'transfer', 'withdraw','remark'];

    public function getNullityTextAttribute()
    {
        return self::NULLITY[$this->nullity ?? self::NULLITY_ON];
    }

    public function getWithdrawTextAttribute()
    {
        return self::WITHDRAW[$this->withdraw ?? self::WITHDRAW_ON];
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改用户状态，用户id为：'.$model->user_id);
        });

    }
}
