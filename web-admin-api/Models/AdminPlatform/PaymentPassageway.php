<?php

namespace Models\AdminPlatform;


class PaymentPassageway extends Base
{
    protected $guarded = [];
    protected $primaryKey = 'id';
    const RECHARGE_AGENTS    = 1;
    const RECHARGE_UNIONS    = 2;
    const RECHARGE_WECHATS   = 3;
    const RECHARGE_ALIPAYS   = 4;
    const PAYMENT_PROVIDERS = 5;
    const ON = 'ON';
    const OFF = 'OFF';
    const STATUS = [
        self::ON => '开启',
        self::OFF => '关闭'
    ];
    // 充值通道权限分类
    const VIP_LISTS = [
        0   => 1,         //未充值
        1   => 2,         //vip1
        2   => 4,         //vip2
        3   => 8,         //vip3
        4   => 16,        //vip4
        5   => 32,        //vip5
        6   => 64,        //vip6
        7   => 128,       //vip7
        8   => 256,       //vip8
        9   => 512,       //vip9
        10  => 1024,      //vip10
        11  => 2048,      //vip11
        12  => 4096,      //vip12
        13  => 8192,      //vip13
        14  => 16384,     //vip14
        15  => 32768,     //vip15
        16  => 65536,     //vip16
        17  => 131072,    //vip17
        18  => 262144,    //vip18
        19  => 524288,    //vip19
        20  => 1048576,   //vip20

    ];
}
