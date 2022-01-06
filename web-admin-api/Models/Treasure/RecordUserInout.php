<?php
/*进出记录表*/
namespace Models\Treasure;

/**
 * ID：              索引标识
 * UserID：          用户标识号码，作为外键与用户数据库的用户标识项关联
 * KindID：          用户进出房间所属游戏的游戏标识
 * ServerID：        用户进出房间所属的房间标识
 * EnterTime：       用户进入房间的时间
 * EnterScore：      用户进入房间时所携带的积分
 * EnterGrade：      用户进入房间时的成绩（未使用）
 * EnterInsure：     用户进入房间时保险箱存款
 * EnterMachine：    用户进入房间时所用电脑的机器码
 * EnterClientIP：   用户进入房间时的IP地址
 * LeaveTime：       用户离开房间的时间
 * LeaveReason：     用户离开房间的原因（0：常规离开 1：系统原因 2：用户冲突 3：网络原因 4：房间人满）
 * LeaveMachine：    用户离开房间时所用电脑的机器码
 * LeaveClientIP：   用户离开房间时的IP地址
 * Score：           成绩变更量
 * Grade：           金币变更量
 * Insure：          保险箱变更量
 * Revenue：         变更税收量
 * WinCount：        胜局变更量
 * LostCount：       输局变更量
 * DrawCount：       和局变更量
 * FleeCount：       逃局变更量
 * PlayTimeCount：   游戏时间
 * OnLineTimeCount： 在线时间
 *
 */
use Models\Accounts\AccountsInfo;
use Models\Platform\GameRoomInfo;
use Models\Platform\GameKindItem;

class RecordUserInout extends Base
{
    protected $table = 'RecordUserInout';
    protected $primaryKey = 'ID';

    /*关联用户*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class,'UserID','UserID');
    }
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
