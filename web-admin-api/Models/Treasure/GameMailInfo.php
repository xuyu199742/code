<?php

namespace Models\Treasure;


use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AdminUser;

class GameMailInfo extends Base
{
    protected $table = 'GameMailInfo';
    protected $primaryKey = 'ID';
    /*用户账号关联*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class, 'UserID', 'UserID');
    }
    /*关联管理员信息表*/
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }
}
