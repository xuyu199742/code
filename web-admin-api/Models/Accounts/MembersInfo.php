<?php

namespace Models\Accounts;


class MembersInfo extends Base
{
    protected $table  = 'MembersInfo';
    public  $timestamps = true;
    public  $guarded = [];

    //最大级别
    const MaxLevel = 20;

    //该等级会员状态
    const STATUS         = [
        self::USING => '启用',
        self::FORBIDDEN  => '禁用',
    ];

    //是否开启关联福利状态
    const RelationStatus         = [
        self::USING => '启用',
        self::FORBIDDEN  => '禁用',
    ];

    const USING = 1;
    const FORBIDDEN  = 0;

    public function accounts()
    {
        return $this->hasMany(AccountsInfo::class, 'MemberOrder', 'MemberOrder')->where('IsAndroid',0);
    }

    public function handselLogs()
    {
        return $this->hasMany(MembersHandselLogs::class, 'MembersID');
    }

    //关联福利配置
    public function membersHandsel()
    {
        return $this->hasMany(MembersHandsel::class,'MembersID','id');
    }

}
