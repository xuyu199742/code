<?php
//记录表
namespace Models\Agent;



use Models\AdminPlatform\AdminUser;

class ChannelWithdrawRecord extends Base
{
    const WAIT_PROCESS = 0;
    const CHECK_FAILS  = 1;
    const CHECK_PASSED = 2;
    const PAY_FAILS    = 3;
    const PAY_SUCCESS  = 4;

    const STATUS = [
        self::WAIT_PROCESS => '等待审核',
        self::CHECK_FAILS  => '审核失败',
        self::CHECK_PASSED => '财务待审核',
        self::PAY_FAILS    => '汇款失败',
        self::PAY_SUCCESS  => '汇款到账'
    ];
    const FINANCE_STATUS = [
        self::CHECK_PASSED => '财务待审核',
        self::PAY_FAILS    => '汇款失败',
        self::PAY_SUCCESS  => '汇款到账'
    ];
    protected $table = 'channel_withdraw_record';
    protected $primaryKey = 'id';

    /* 状态*/
    public function getStatusTextAttribute()
    {
        return self::STATUS[$this->status] ?? '';
    }
    /*关联操作人*/
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }
}
