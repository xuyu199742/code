<?php

namespace Models\AdminPlatform;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $guarded = ['id'];

    const STATUS_ON  = 1;
    const STATUS_OFF = 0;
    const STATUS     = [
        self::STATUS_ON  => '开启',
        self::STATUS_OFF => '关闭'
    ];

    public function getStatusTextAttribute()
    {
        return self::STATUS[$this->status] ?? '';
    }

   /* public function getRechargeStatusTextAttribute(){
        return self::STATUS[$this->recharge_status] ?? '';
    }

    public function getWithdrawalStatusTextAttribute(){
        return self::STATUS[$this->withdrawal_status] ?? '';
    }*/
}
