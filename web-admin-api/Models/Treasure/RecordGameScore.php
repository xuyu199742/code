<?php
/*游戏房间成绩历史统计表*/
namespace Models\Treasure;
/*
 * ID
 * UserID
 * ServerID
 * ServerLevel
 * KindID
 * ChangeScore              变化数(玩家输赢)
 * JettonScore              下注量
 * SystemScore              系统输赢
 * SyetemServiceScore       服务费
 * UpdateTime
 * CurrentScore             操作后积分
 *
 */
use Models\Accounts\AccountsInfo;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
class RecordGameScore extends Base
{
    protected $table = 'RecordGameScore';

    /*游戏记录--用户关联*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class,'UserID','UserID');
    }

    /*游戏记录--游戏关联*/
    public function kind()
    {
        return $this->belongsTo(GameKindItem::class,'KindID','KindID');
    }

    /*游戏记录--房间关联*/
    public function server()
    {
        return $this->belongsTo(GameRoomInfo::class,'ServerID','ServerID');
    }
    /*统计玩家输赢*/
    public static function sumWinLose($user_id,$is_today = false)
    {
        $data= RecordGameScore::where('UserID', $user_id);
        if ($is_today === true){
            $data->whereDate('UpdateTime', date("Y-m-d"));
        }
        return $data->sum('ChangeScore');
    }
}
