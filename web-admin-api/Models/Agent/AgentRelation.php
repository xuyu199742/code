<?php
//用户推广记录表
namespace Models\Agent;

use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\RecordScoreDaily;

class AgentRelation extends Base
{
    protected $table = 'agent_relation';
    protected $fillable = ['user_id','parent_user_id','rank','created_at'];
    public    $timestamps = false;
    /*关联用户*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class,'user_id','UserID');
    }

    /*关联用户代理信息表*/
    public function agentinfo()
    {
        return $this->belongsTo(AgentInfo::class,'user_id','user_id');
    }

    /*关联父级代理用户用户*/
    public function agentAccount()
    {
        return $this->belongsTo(AccountsInfo::class,'parent_user_id','UserID');
    }

    /*关联用户充值*/
    public function payment()
    {
        return $this->hasMany(PaymentOrder::class, 'user_id','user_id');
    }


    public function withdraw()
    {
        return $this->hasMany(WithdrawalOrder::class, 'user_id','user_id');
    }

    /*关联用户代理*/
    public function agentWithdraw()
    {
        return $this->hasMany(AgentWithdrawRecord::class, 'user_id','user_id');
    }

    /*关联首充*/
    public function firstPay()
    {
        return $this->hasMany(FirstRechargeLogs::class,'user_id','user_id');
    }

    /*关联游戏记录日流水表*/
    public function dayWater()
    {
        return $this->hasMany(RecordScoreDaily::class,'UserID','user_id');
    }

    /*关联金币记录*/
    public function goldWater()
    {
        return $this->hasMany(RecordTreasureSerial::class,'UserID','user_id');
    }

    /*关联直属下级*/
    public function subAgent()
    {
        return $this->hasMany($this,'parent_user_id','user_id');
    }

    /*关联佣金记录表*/
    public function incomes()
    {
        return $this->hasMany(AgentIncome::class,'user_id','user_id');
    }
}
