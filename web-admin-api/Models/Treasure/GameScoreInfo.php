<?php
/*金币信息表*/
namespace Models\Treasure;

/**
 * UserID：              用户标识号码，作为外键与用户数据库用户标识项关联
 * Score：               用户的金币或积分数值，此处代表用户的金币数值
 * Revenue：             游戏税收，用户在游戏中被系统扣除税收的累计总和
 * InsureScore：         保险箱存款金币，（保留扩展用字段），有运营商确定
 * JettonScore
 * WinCount：            用户在使用本数据库的房间里游戏胜利总局数
 * LostCount：           用户的游戏输局局数
 * DrawCount：           用户的游戏和局局数
 * FleeCount：           用户的游戏逃跑局数
 * UserRight             用户权限
 * MasterTight           管理权限
 * MasterOrder           管理等级
 * AllLogonTimes         总登陆次数
 * PlayTimeCount         游戏时间
 * OnlineTimeCount       在线时间
 * LastLogonIP           上次登陆 IP
 * LastLogonDate         上次登陆时间
 * LastLogonMachine      登录机器
 * RegisterIP            注册IP
 * RegisterDate          注册时间
 * RegisterMachine       注册机器
 * WinScore              输赢积分
 */
use Models\Accounts\AccountsInfo;

class GameScoreInfo extends Base
{
    protected $table = 'GameScoreInfo';
    protected $primaryKey = 'UserID';

    /*用户--金币信息关联*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class,'UserID','UserID');
    }

    /*增加用户金币数量*/
    public function addUserScore($user_id,$score)
    {
        return $this->where('UserID',$user_id)->increment('Score', $score);
    }
}
