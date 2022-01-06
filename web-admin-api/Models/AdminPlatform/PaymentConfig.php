<?php

namespace Models\AdminPlatform;


class PaymentConfig extends Base
{
    const ON = 'ON';
    const OFF = 'OFF';
    const STATUS = [
        self::ON => '开启',
        self::OFF => '关闭'
    ];

    /*关联通道*/
    public function providers()
    {
        return $this->hasMany(PaymentProvider::class,'payment_config_id','id');
    }

    /**
     * 关联订单
     * 远程一对一
     */
    public function orders()
    {
        return $this->hasManyThrough(
            PaymentOrder::class,// 远程表
            PaymentProvider::class,// 中间表
            'payment_config_id',// 中间表对主表的关联字段
            'payment_provider_id',// 远程表对中间表的关联字段
            'id',// 主表对中间表的关联字段
        'id'// 中间表对远程表的关联字段
            )
            ->whereNotIn('payment_type',['official_wechat','official_alipay','official_union','vip_business']);
    }
}
