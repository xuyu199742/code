<?php
/*游戏种类列表*/
namespace Models\Platform;
/**
 * KindID：      游戏类型的标识号码，应该与游戏的开发所分配的标识号码所一致。也可以不一致，通过配置游戏房间的时候配置房间的挂接项，达到同一个游戏服务器挂接到不同的游戏类型上面，实现金币类，比赛类，积分类等扩展显示方式。
 * GameID：      游戏类型的标识号码，一般与游戏的开发所分配的标识号码所一致。也可以不一致。如果不一致的话，房间里的类型表示必须要修改的和这里一样。否则无法在改节点下显示对应的房间
 * TypeID：      游戏类型所挂接的游戏类型标识号码，需要保证所对于的类型的标识号码存在，并所对应的类型行是启用状态，否则游戏大厅的游戏列表不会显示此游戏类型以及挂接在此游戏类型下的所有房间
 * JoinID：      指定此分级所挂接的分级的标识号码，用于控制多层分级使用，默认为 0 数值。对应GameNodeItem表的NodeID
 * SortID：      列表排列 ID 号码，用于控制游戏大厅得到的列表的排列方式
 * KindName：    游戏类型名字，例如梭哈游戏，斗地主游戏等
 * ProcessName： 游戏进程名字，用于控制客户端启动的游戏进程的名字
 * GameRuleUrl： 游戏规则页面地址
 * DownLoadUrl： 游戏安装包下载地址
 * Recommend：   推荐游戏（是否为推荐游戏）
 * GameFlag：    游戏标志(1:新2荐3热4赛)
 * Nullity：     是否显示控制字段，默认为 1，禁止显示为 0 数值
 */
use Models\AdminPlatform\SystemLog;
use Models\Treasure\GameScoreLocker;
use Models\Treasure\RecordGameScore;
class GameKindItem extends Base
{
    protected $table = 'GameKindItem';
    protected $primaryKey = 'KindID';

    /*游戏关联多个房间*/
    public function rooms()
    {
        return $this->hasMany(GameRoomInfo::class, 'KindID', 'KindID');
    }

    /**
     * 游戏通关房间关联游戏记录的远程一对多的关联关系
     */
    public function records()
    {
        return $this->hasManyThrough(GameRoomInfo::class, RecordGameScore::class);
    }

    public function online()
    {
        return $this->hasManyThrough(GameScoreLocker::class,GameRoomInfo::class, 'KindID','ServerID','KindID','KindID');
    }
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改游戏配置，游戏标识为：'.$model->KindID);
        });
    }

}
