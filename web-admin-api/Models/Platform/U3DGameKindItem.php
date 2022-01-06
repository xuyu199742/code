<?php
/*U3D游戏列表*/
namespace Models\Platform;
use Models\AdminPlatform\SystemLog;
class U3DGameKindItem extends Base
{
    protected $table = 'U3DGameKindItem';
    protected $primaryKey = 'KindID';

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改U3D游戏配置，游戏标识为：'.$model->KindID);
        });
    }
}
