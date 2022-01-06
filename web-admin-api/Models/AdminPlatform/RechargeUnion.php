<?php

namespace Models\AdminPlatform;


class RechargeUnion extends Base
{
    const SIGN   = 'official_union';
    const ON     = 1;
    const OFF    = 0;
    const STATUS = [
        self::ON  => '开启',
        self::OFF => '禁用',
    ];
    const BANK   = [
        1 => '交通银行',
        2 => '工商银行',
        3 => '招商银行',
        4 => '建设银行',
        5 => '邮政储蓄银行',
        6 => '农业银行',
        7 => '中国银行'
    ];
}
