<?php

namespace Models\Record;

/**
 * ID：              序列标识
 * UserID：          用户标识
 * CreateDate：      日期内登录次数
 * CreateDate：      登录日期
 *
**/
use Models\Accounts\AccountsInfo;

class RecordUserLogon extends Base
{
    protected $table='RecordUserLogon';

    /*关联用户*/
    public function accounts()
    {
        return $this->belongsTo(AccountsInfo::class,'UserID','UserID');
    }
}
