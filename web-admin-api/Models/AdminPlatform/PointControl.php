<?php

namespace Models\AdminPlatform;


class PointControl extends Base
{
    protected $table      = 'point_control';
    protected $guarded    = [];
    public    $timestamps = false;
    protected $primaryKey = 'id';
    const FIXED_GOLD   = 1;
    const FIXED_NUMBER = 2;
    const CONTROL_TYPE= [
        self::FIXED_GOLD   => '固定金币',
        self::FIXED_NUMBER => '固定局数',
    ];
    const WIN_TARGET   = 1;
    const LOSE_TARGET  = 2;
    const TARGET_TYPES = [
        self::WIN_TARGET   => '胜',
        self::LOSE_TARGET => '负',
    ];
    const PRIORITY = [
        'PRIORITY_ONE'   => 1,
        'PRIORITY_TWO'   => 2,
        'PRIORITY_THREE' => 3,
        'PRIORITY_FOUR'  => 4,
    ];
    const NORMAL         = 0;
    const COMPLETE       = 1;
    const HALFWAY_DELETE = 2;
    const STATUS = [
        self::NORMAL         => '正常',
        self::COMPLETE       => '控制完成',
        self::HALFWAY_DELETE => '中途删除',
    ];
    const RECORDS_STATUS = [
        self::COMPLETE       => '控制完成',
        self::HALFWAY_DELETE => '中途删除',
    ];
    /* 状态*/
    public function getStatusTextAttribute()
    {
        return self::STATUS[$this->status] ?? '';
    }
}
