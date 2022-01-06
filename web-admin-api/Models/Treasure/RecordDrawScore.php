<?php
/*游戏记录从表*/
namespace Models\Treasure;

use Models\Accounts\AccountsInfo;

/**
 * DrawID               int         局数标识
 * UserID               int         用户标识
 * ChairID              int         椅子号码
 * Score                bigint      用户成绩
 * Grade                bigint      用户积分
 * Revenue              bigint      税收数目
 * PlayTimeCount        int         游戏时长
 * DBQuestID            int         请求标识
 * InoutIndex           int         进出索引
 * InsertTime           datetime    插入时间（及游戏结束时间）
 */

class RecordDrawScore extends Base
{
    protected $table = 'RecordDrawScore';

    /*关联玩家日志主表*/
    public function darwInfo()
    {
        return $this->belongsTo(RecordDrawInfo::class,'DrawID','DrawID');
    }
    /*游戏记录--用户关联*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class, 'UserID', 'UserID');
    }
}
