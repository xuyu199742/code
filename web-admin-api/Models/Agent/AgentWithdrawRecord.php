<?php
//保险箱记录表
namespace Models\Agent;


use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AdminUser;

class AgentWithdrawRecord extends Base
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
    protected $table = 'agent_withdraw_record';
    protected $primaryKey = 'id';

    /*状态*/
    public function getStatusTextAttribute()
    {
        return self::STATUS[$this->status] ?? '';
    }
    /*关联操作人*/
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }

    /*关联用户*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class,'user_id','UserID');
    }

    //新增领取记录（原代理提现记录）
    public static function add($user_id,$score,$name = '',$phonenum = '',$backname = '',$backcard = '')
    {
        $model = new self();
        $model->user_id   = $user_id ?? 0;//用户id
        $model->order_no  = date('YmdHis',time()).rand(1000,9999);//订单号
        $model->score     = $score ?? 0;
        $model->name      = $name ?? '';//用户名称
        $model->phonenum  = $phonenum ?? '';//手机号
        $model->back_name = $backname ?? '';//名称
        $model->back_card = $backcard ?? '';//卡号
        $model->status    = 0;
        return $model->save();
    }
}
