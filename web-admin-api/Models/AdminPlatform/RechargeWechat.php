<?php

namespace Models\AdminPlatform;


class RechargeWechat extends Base
{
    const SIGN   = 'official_wechat';
    const ON     = 1;
    const OFF    = 0;
    const STATUS = [
        self::ON  => '开启',
        self::OFF => '禁用',
    ];
}
