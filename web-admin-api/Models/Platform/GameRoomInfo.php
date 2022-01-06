<?php
/*游戏房间列表*/
namespace Models\Platform;

/**
 * ServerID：                游戏房间标识。由系统生成。必须为唯一值
 * ServerName：              房间名
 * KindID：                  房间所属游戏类型
 * NodeID：                  房间所挂载的节点ID
 * SortID：                  排序ID
 * GameID：                  房间所挂载的模块标识
 * TableCount：              房间拥有桌子数目
 * ServerKind：              游戏房间挂接类型
 * ServerType：              房间游戏类型
 * ServerPort：              房间所占用端口
 * ServerLevel：             手机房间名称标识（例：0练习房1初级房2中级房）
 * ServerPasswd：            密码房间进入密码
 * DataBaseName：            房间使用的数据库名称
 * DataBaseAddr：            房间所使用的数据库地址
 * CellScore：               房间单元积分
 * RevenueRatio：            房间收税比例（单位：千分比）
 * ServiceScore：            服务费（服务费1：1的抽水模式）
 * RestrictScore：           房间限制积分
 * MinTableScore：           房间坐下最小积分
 * MinEnterScore：           房间进入最小积分
 * MaxEnterScore：           房间进入最大积分
 * MinEnterMember：          房间进入最低会员级别
 * MaxEnterMember:           房间进入最高会员级别
 * MaxPlayer：               最大游戏人数
 * ServerRule：              房间规则
 * DistributeRule：          分组规则
 * MinDistributeUser：       分组人数
 * DistributeTimeSpace：     分组间隔
 * DistributeDrawCount：     分组局数
 * MinPartakeGameUser：      游戏最少人数
 * MaxPartakeGameUser：      游戏最多人数
 * AttachUserRight：         用户在房间所拥有的附加权限（比如允许动态加入、禁止大厅聊天等）
 * ServiceMachine：          使用该房间服务器的机器码
 * CustomRule：              自定义规则。
 * PersonalRule：            私人房规则
 * Nullity：                 房间是否能使用
 * ServerNote：              房间备注信息。不参与游戏和前台显示。
 * CreateDateTime：          房间创建日期
 * ModifyDateTime：          房间最新修改日期
 * EnterPassword：           进入密码
 */

use Models\Treasure\RecordGameScore;
use Models\Treasure\GameScoreLocker;

class GameRoomInfo extends Base
{
    protected $table = 'GameRoomInfo';
    protected $primaryKey = 'ServerID';
    /*房间关联游戏记录*/
    public function records()
    {
        return $this->hasMany(RecordGameScore::class,'ServerID','ServerID');
    }
    /*房间关联在线人数*/
    public function gameScoreLocker()
    {
        return $this->hasMany(GameScoreLocker::class,'ServerID','ServerID');
    }
}
