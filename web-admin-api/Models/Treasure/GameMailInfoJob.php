<?php

namespace Models\Treasure;

use Models\AdminPlatform\AdminUser;

class GameMailInfoJob extends Base
{
    protected $table = 'GameMailInfoJob';
    protected $primaryKey = 'ID';

    /*单条保存*/
    public static function saveOne($info)
    {
        $model             = new self();
        $model->ChannelID  = 0;//所有的用户发送
        $model->Title      = $info['Title'] ?? '';
        $model->Context    = $info['Context'] ?? '';
        $model->CreateTime = date('Y-m-d H:i:s');//创建时间
        $model->StartTime  = $info['StartTime'] ?? $model->CreateTime;//有效期开始时间
        $model->EndTime    = date('Y-m-d H:i:s', strtotime ("+7 day", strtotime($model->StartTime)));//有效期结束时间，7天内有效
        $model->admin_id      = $info['admin_id'];
        return $model->save();
    }
    /*关联管理员信息表*/
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }
}
