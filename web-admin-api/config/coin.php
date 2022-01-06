<?php
/**
 * 站点金币充值设置
 */
return [
    'db_coin_base_ratio' => 10000, //数据库中的初始比值
    'recharge_ratio'     => 1, //金币值，充值比例1金币=1 此处填写金币值，非货币值
    'recharge_status'    => 1, //充值开关，1开，0关
    'withdrawal_ratio'   => 1, //金币值，比例1金币:1 此处填写金币值，非货币值
    'withdrawal_status'  => 1, //开关，1开，0关
    'min_withdrawal'     => 10, //最小量
    'withdrawal_desc'    => '1.单次提现最低10W金币起，最大不可超过下注总额。
2.每次提现后，下注总额会扣除提现金币额。
3.提现兑换比为1W:1,及1W金币可获得1元。
4.提现申请提交后，兑换金额将暂时从携带数额中扣除，进入托管状态，提现成功后，正常扣除。如果提现申请取消或者兑换失败，托管状态的金额会返还给玩家。
5.用户的支付信息请填写正确，如果有误，客户会和玩家联系，联系不上的提现申请将被驳回。',
    'locked'=>['db_coin_base_ratio','recharge_ratio','withdrawal_ratio']
];
