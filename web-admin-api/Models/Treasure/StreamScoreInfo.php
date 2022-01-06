<?php
/*用户输赢日统计表---局数*/
namespace Models\Treasure;

/**
 * DateID：              日期标示
 * UserID：              用户标识
 * WinCount：            当日对该用户赢局总局数
 * LostCount：           当日对该用户输局总局数
 * Revenue：             当日对该用户收取的总税收
 * PlayTimeCount：       当日对该用户总游戏时长（秒）
 * OnlineTimeCount：     当日对该用户总在线时长（秒）
 * FirstCollectDate：    开始统计时间
 * LastCollectDate：     最后统计时间
 */

class StreamScoreInfo extends Base
{
    protected $table = 'StreamScoreInfo';
    protected $primaryKey = 'UserID';
}
