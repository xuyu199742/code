<?php
/*游戏记录从表2，最新30条记录*/
namespace Models\Treasure;

/**
 * DrawID               int         局数标识
 * UserID               int         用户标识
 * KindID               int         游戏标识
 * ChairID              int         椅子号码
 * Score                bigint      用户成绩
 * Grade                bigint      用户积分
 * Revenue              bigint      税收数目
 * PlayTimeCount        int         游戏时长
 * DBQuestID            int         请求标识
 * InoutIndex           int         进出索引
 * InsertTime           datetime    插入时间（及游戏结束时间）
 * ServerLevel          int         房间类型：1、初级场，2、中级场，3、高级场....默认0为空
 */

class RecordDrawScoreForWeb extends Base
{
    protected $table = 'RecordDrawScoreForWeb';
}
