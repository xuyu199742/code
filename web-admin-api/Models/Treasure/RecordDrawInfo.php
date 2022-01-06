<?php
/*游戏记录主表*/
namespace Models\Treasure;
/**
 * DrawID               int         局数标识
 * KindID               int         游戏标识
 * ServerID             int         房间标识
 * TableID              int         桌子号码
 * UserCount            int         用户数目
 * AndroidCount         int         机器数目
 * Waste                bigint      损耗数目
 * Revenue              bigint      税收数目
 * StartTime            datetime    开始时间
 * ConcludeTime         datetime    结束时间
 * InsertTime           datetime    插入时间
 * DrawCourse           image       游戏过程
 */
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;


class RecordDrawInfo extends Base
{
    protected $table = 'RecordDrawInfo';

    /*关联游戏*/
    public function kind()
    {
        return $this->belongsTo(GameKindItem::class,'KindID','KindID');
    }

    /*关联房间*/
    public function server()
    {
        return $this->belongsTo(GameRoomInfo::class,'ServerID','ServerID');
    }
}
