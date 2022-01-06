<?php
/*游戏房间成绩历史统计表*/
namespace Models\Treasure;

/*
 * UserID：          用户标识
 * KindID：          游戏标识
 * ServerID：        房间标识
 * IntegralCount：   历史积分（单个房间）
 * WinCount：        胜局数目（单个房间）
 * LostCount：       输局数目（单个房间）
 * DrawCount：       和局数目（单个房间）
 * FleeCount：       逃跑局数（单个房间）
 *
 */

class GameScoreAttribute extends Base
{
    protected $table = 'GameScoreAttribute';
}
