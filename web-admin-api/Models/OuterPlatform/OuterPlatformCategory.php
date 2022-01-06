<?php

namespace Models\OuterPlatform;

class OuterPlatformCategory extends Base
{
    protected $table    = 'outer_platform';
    public $timestamps  = false;
    const STATUS_ON   = 1;
    const STATUS_OFF  = 2;
    const STATUS      = [
        self::STATUS_ON  => '启用',
        self::STATUS_OFF => '禁用',
    ];

    public function getStatusTextAttribute()
    {
        return self::STATUS[$this->status] ?? '';
    }

}
