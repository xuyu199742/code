<?php
/*转盘配置表*/
namespace Models\Activity;


class TurntableConfig extends Base
{
    //数据表
    protected $table = 'TurntableConfig';
    protected $primaryKey = 'id';
    //转盘类型
    const BET_TYPE = 0; // 投注
    const RECHARGE_TYPE  = 1; //充值
    //转盘区域数
    const RECORD_NUM =12; //一个转盘12个区域数
}
