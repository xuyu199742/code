<?php
/* 用户转盘记录*/
namespace Models\Activity;


use Models\Accounts\AccountsInfo;

class UserRotaryRecords extends Base
{
    //数据表
    protected $table = 'user_rotary_records';

    public $guarded = [];

    public function Accounts()
    {
        return $this->belongsTo(AccountsInfo::class,'user_id', 'UserID');
    }

    public function ActivitiesNormal()
    {
        return $this->belongsTo(ActivitiesNormal::class, 'activity_normal_id', 'id');
    }
}
