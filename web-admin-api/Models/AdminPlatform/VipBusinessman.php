<?php

namespace Models\AdminPlatform;

use Models\Accounts\AccountsInfo;
use Models\Treasure\GameScoreInfo;

class VipBusinessman extends Base
{
    const SIGN='vip_business';
    protected $table = 'vip_businessman';  // vip商人
    const NULLITY_ON   = 0;
    const NULLITY_OFF  = 1;
    const NULLITY      = [
        self::NULLITY_ON  => '启用',
        self::NULLITY_OFF => '禁用',
    ];
    const TYPE_ONE   = 1;
    const TYPE_TWO   = 2;
    const TYPE       = [
        self::TYPE_ONE  => '微信号',
        self::TYPE_TWO  => 'QQ号',
    ];
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }
    //添加日志
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改VIP商人信息，id为：'.$model->id);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除VIP商人信息，id为：'.$model->id);
        });
    }

    public function score(){
        return $this->hasOne(GameScoreInfo::class,'UserID','user_id');
    }

    public function account(){
        return $this->belongsTo(AccountsInfo::class,'user_id','UserID');
    }


}
