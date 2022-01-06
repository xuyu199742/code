<?php
/*
 |--------------------------------------------------------------------------
 | 定义支付类型接口
 |--------------------------------------------------------------------------
 | Notes:
 | Class PayTypes
 | User: Administrator
 | Date: 2019/7/15
 | Time: 15:17
 |
 |  * @return
 |  |
 |
 */

namespace Modules\Payment\Packages\ThirdPay\Interfaces;

interface PayTypes
{
    const WECHAT_QRCODE     = 'wechat_qecode';
    const WECHAT_H5         = 'wechat_h5';
    const ALIPAY_QRCODE     = 'alipay_qecode';
    const ALIPAY_H5         = 'alipay_h5';
    const YUNSHANFU         = 'yunshanfu';
    const UNIONPAY_QRCODE   = 'unionpay_qrcode';
    const ALIPAY_SDK        = 'alipay_sdk';
    const ONLINE_BANKING    = 'online_banking';

    const TABS = [
        self::WECHAT_QRCODE     => '微信扫码',
        self::WECHAT_H5         => '微信H5',
        self::ALIPAY_QRCODE     => '支付宝扫码',
        self::ALIPAY_H5         => '支付宝H5',
        self::YUNSHANFU         => '云闪付',
        self::UNIONPAY_QRCODE   => '银联',
        self::ALIPAY_SDK        => '支付宝SDK',
        self::ONLINE_BANKING    => '网银',
    ];

    //返回sdk的平台名称
    public static function name();

    //定义接口及类型
    public static function apis();
}
