<?php
/* 任务活动配置 */
namespace Models\Activity;

use Illuminate\Database\Eloquent\SoftDeletes;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;

class ActivityTaskConfig extends Base
{
    use SoftDeletes;
    //数据表
    protected $table = 'ActivityTaskConfig';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    //任务类型
    const MATCHES_NUM    = 1;
    const TASK_TYPE = [
        self::MATCHES_NUM     => '对局数',
    ];
    //获取任务类型名称
    public function getCategoryTextAttribute()
    {
        return isset(self::TASK_TYPE[$this->category]) ? self::TASK_TYPE[$this->category] : '';
    }
    /*关联游戏*/
    public function kind()
    {
        return $this->belongsTo(GameKindItem::class,'KindID','kind_id');
    }
    /*关联房间*/
    public function server()
    {
        return $this->belongsTo(GameRoomInfo::class,'ServerID','ServerID');
    }
}
