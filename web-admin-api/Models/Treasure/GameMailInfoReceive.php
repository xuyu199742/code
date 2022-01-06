<?php
//用户发送邮件记录表
namespace Models\Treasure;

use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AdminUser;

class GameMailInfoReceive extends Base
{
    protected $table = 'GameMailInfoReceive';
    protected $primaryKey = 'ID';
    const REPLAY_YES = 1;
    const REPLAY_NO  = 0;
    const REPLAY_STATUS    = [
        self::REPLAY_YES => '已回复',
        self::REPLAY_NO  => '未回复'
    ];
    /*单条保存*/
    public static function saveOne()
    {
        $model = new self();
        $model->UserID = request('UserID');//所有的用户发送
        $model->Title = request('Title') ?? '';
        $model->Context = request('Context') ?? '';
        $model->IsRead = 0;//是否已读，0未读，1已读
        $model->IsDelete = 0;//是否删除，0正常，1删除
        $model->IsReply = 0;//是否回复，0未回复，1已回复
        $model->CreateTime = date('Y-m-d H:i:s');//创建时间
        return $model->save();
    }
    /*用户关联*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class, 'UserID', 'UserID');
    }
    /*关联管理员信息表*/
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');

    }
}
