<?php

namespace Models\AdminPlatform;


class RechargeAlipay extends Base
{
    const SIGN   = 'official_alipay';
    const ON     = 1;
    const OFF    = 0;
    const STATUS = [
        self::ON  => '开启',
        self::OFF => '禁用',
    ];
}
