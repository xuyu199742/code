<?php

namespace Models\AdminPlatform;


class PaymentWay extends Base
{
    protected $guarded = [];
    protected $primaryKey = 'id';
    const ON = 'ON';
    const OFF = 'OFF';
    const STATUS = [
        self::ON => '开启',
        self::OFF => '关闭'
    ];
}
