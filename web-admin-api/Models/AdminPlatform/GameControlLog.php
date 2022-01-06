<?php

namespace Models\AdminPlatform;


use Illuminate\Support\Facades\Auth;

class GameControlLog extends Base
{
    protected $table      = 'game_control_log';
    public    $timestamps = false;
    const USER_SCORE_UP    = 1;
    const USER_SCORE_DOWN  = 2;
    const BIND_CORD_NUMBER = 3;
    const ADD_AUDITBET     = 4;
    const REDUCE_AUDITBET  = 5;
    const CHANGE_PASSWORD  = 6;
    const GAME_CONTROL     = 9;
    //动作类型
    const ACTION_TYPES  = [
        self::USER_SCORE_UP    => '玩家上分',
        self::USER_SCORE_DOWN  => '玩家下分',
        self::BIND_CORD_NUMBER => '绑定卡号',
        self::ADD_AUDITBET     => '增加稽核',
        self::REDUCE_AUDITBET  => '减少稽核',
        self::CHANGE_PASSWORD  => '修改密码',
        self::GAME_CONTROL     => '游戏控制',
    ];
    //状态
    const SUCCESS = 1;
    const FAILS   = 0;
    const STATUS = [
        self::SUCCESS => '成功',
        self::FAILS   => '失败',
    ];
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }

    public static function addOne($title,$details,$action=self::GAME_CONTROL,$status=self::SUCCESS)
    {
        $model              = new self();
        $model->admin_id    = Auth::guard('admin')->id() ?? 0;
        $model->ip          = request()->ip();
        $model->create_time = date('Y-m-d H:i:s');
        $model->details     = $details;
        $model->title       = $title;
        $model->action      = $action;
        $model->status      = $status;
        return $model->save();
    }
    //获取状态名称
    public function getStatusTextAttribute()
    {
        return isset(self::STATUS[$this->status]) ? self::STATUS[$this->status] : '';
    }
}
