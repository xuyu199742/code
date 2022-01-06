<?php
/* 活动配置 */
namespace Models\Activity;


class Activity extends Base
{
    protected $table = 'Activity';
    //活动类型
    const TASK_ACTIVITY     = 1;
    const REBATE_ACTIVITY   = 2;
    const RANKING_ACTIVITY  = 3;

    const ACTIVITY_TYPE = [
        self::TASK_ACTIVITY       => '任务活动',
        self::REBATE_ACTIVITY     => '返利活动',
        self::RANKING_ACTIVITY    => '排行活动',
    ];

}
