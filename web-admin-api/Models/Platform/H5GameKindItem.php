<?php
/*H5游戏列表*/
namespace Models\Platform;
use Models\AdminPlatform\SystemLog;
class H5GameKindItem extends Base
{
    protected $table = 'H5GameKindItem';
    protected $primaryKey = 'KindID';
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改H5游戏配置，游戏标识为：'.$model->KindID);
        });
    }
}
