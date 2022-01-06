<?php

namespace Models\AdminPlatform;


class RechargeAgent extends Base
{
    const SIGN   = 'official_agent';
    const ON     = 1;
    const OFF    = 0;
    const STATUS = [
        self::ON  => '开启',
        self::OFF => '禁用',
    ];
    const TYPE   = [
        1 => 'QQ',
        2 => '微信号',
        3 => '支付宝'
    ];
}
