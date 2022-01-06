<?php
/*锁定游戏用户*/
namespace Models\Treasure;
/**
 * UserID：          锁定用户的 ID 号码，作为外键与用户数据库的用户标识项关联
 * KindID：          锁定游戏的游戏类型标识号码，用于追踪和查询使用，不起关键使用
 * ServerID：        锁定游戏的房间标识号码，用于追踪和查询使用，不起关键使用
 * EnterID：         进出索引
 * EnterIP：         登录的时候的IP
 * EnterMachine：    登录时候电脑的机器码
 * CollectDate：     记录日期
 */
use Models\Accounts\AccountsInfo;
use Models\Agent\ChannelUserRelation;
use Models\Platform\GameRoomInfo;
use Models\Platform\GameKindItem;
class GameScoreLocker extends Base
{
    protected $table = 'GameScoreLocker';

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

    /*关联房间*/
    public function channel()
    {
        return $this->hasOne(ChannelUserRelation::class,'user_id','UserID');
    }
}
