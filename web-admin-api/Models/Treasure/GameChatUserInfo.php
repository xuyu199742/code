<?php
/*锁定游戏用户*/
namespace Models\Treasure;

/*
 * UserID：          用户的 ID 号码，作为外键与用户数据库的用户标识项关联
 * CollectDate：     记录日期
 *
 */

use Models\Accounts\AccountsInfo;
use Models\Agent\ChannelUserRelation;
use Models\Platform\GameRoomInfo;
use Models\Platform\GameKindItem;

class GameChatUserInfo extends Base
{
    //数据表
    protected $table = 'GameChatUserInfo';

    /*用户主表关联*/
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

    /*关联渠道*/
    public function channel()
    {
        return $this->hasOne(ChannelUserRelation::class,'user_id','UserID');
    }
}
