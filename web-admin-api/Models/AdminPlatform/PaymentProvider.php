<?php

namespace Models\AdminPlatform;

class PaymentProvider extends Base
{
    const ON = 'ON';
    const OFF = 'OFF';
    const STATUS = [
        self::ON => '开启',
        self::OFF => '关闭'
    ];

    public function config(){
        return $this->hasOne(PaymentConfig::class,'id','payment_config_id');
    }

    public function payment()
    {
        return $this->hasMany(PaymentOrder::class,'payment_provider_id','id');
    }

}
