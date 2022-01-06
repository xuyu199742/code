<?php
/* 活动配置 */
namespace Models\Activity;


class ActivityConfig extends Base
{
    //数据表
    protected $table = 'ActivityConfig';
    //转盘类型
    const BET_TYPE = 0;  // 投注转盘
    const RECHARGE_TYPE  = 1;  //充值转盘
    const TURNTABLE_TYPE    = [
        self::BET_TYPE => '0',
        self::RECHARGE_TYPE   => '1'
    ];
    //状态类型
    const STATUS_ON   = 1; //启用
    const STATUS_OFF  = 0; //禁用
    const RED_PACKET_TYPE  = 2; //红包
}
