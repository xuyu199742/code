<?php
/* 用户转盘信息*/

namespace Models\Activity;


class UserRotaryInfo extends Base
{
    //数据表
    protected $table = 'user_rotary_info';

    public $guarded = [];

    //跨天重置
    public static function dayReset($user_id)
    {
        UserRotaryInfo::whereDate('updated_at','<', date('Y-m-d'))
            ->where('user_id',$user_id)
            ->update(['used_value'=>0,'updated_at'=>date('Y-m-d H:i:s')]);
    }
}
