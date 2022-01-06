<?php

namespace Models\AdminPlatform;


class WhiteIp extends Base
{
    const NULLITY_ON   = 0;
    const NULLITY_OFF  = 1;
    const NULLITY      = [
        self::NULLITY_ON  => '启用',
        self::NULLITY_OFF => '禁用',
    ];
    protected $table  = 'white_ip';
    //添加日志
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改IP白名单，操作用户id为：'.$model->id);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除IP白名单，操作用户id为：'.$model->id);
        });
    }
}
