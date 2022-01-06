<?php

namespace Models\Platform;
use Illuminate\Support\Facades\Auth;
use Models\AdminPlatform\SystemLog;


class SystemMessage extends Base
{
    //数据表
    protected $table = 'SystemMessage';
    protected $primaryKey='ID';
    /*  消息范围(1:游戏,2:房间,3:全部) */
    const MESSAGE_GAME = 1;
    const MESSAGE_ROOM = 2;
    const MESSAGE_ALL  = 3;
    const MESSAGE_TYPE = [
        self::MESSAGE_GAME => '游戏',
        self::MESSAGE_ROOM  => '房间',
        self::MESSAGE_ALL => '全部'
    ];
    /* 系统消息状态类型*/
    const NULLITY_ON        = 0;
    const NULLITY_OFF       = 1;
    const NULLITY           = [
        self::NULLITY_ON    => '正常',
        self::NULLITY_OFF   => '禁用',
    ];
    public static function saveRecord($info,$MessageId = null)
    {
        if ($MessageId){
            //编辑
            $model  = self::find($MessageId);
            if (!$model) {
                return false;
            }
        }else{
            //新增
            $model  = new self();
        }
        $model -> MessageType      = $info['MessageType'] ?? 3;
        $model -> ServerRange      = $info['ServerRange'] ?? 0;
        $model -> MessageString    = $info['MessageString'];
        $model -> StartTime        = $info['StartTime'] ?? 0;
        $model -> ConcludeTime     = $info['ConcludeTime'] ?? 0;
        $model -> TimeRate         = $info['TimeRate'];
        $model -> Nullity          = $info['Nullity'] ?? self::NULLITY_OFF;
        $model -> CreateDate       = date('Y-m-d H:i:s',time());
        $model -> CreateMasterID   = Auth::guard('admin')->id() ?? 0;
        $model -> UpdateDate       = date('Y-m-d H:i:s',time());
        $model -> UpdateMasterID   = Auth::guard('admin')->id() ?? 0;
        $model -> CollectNote      = '';
        return $model->save();
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改系统消息，标识为：'.$model->ID);
        });
    }
}
