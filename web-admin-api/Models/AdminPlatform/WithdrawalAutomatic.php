<?php

namespace Models\AdminPlatform;


class WithdrawalAutomatic extends Base
{
    protected $table = 'withdrawal_automatic';
    protected $primaryKey = 'id';
    /* 定义子状态 */
    const FINANCE_CHECK = 1;
    const AUTOMATIC_PAYMENT = 2;
    const AUTOMATIC_SUCCESS = 3;
    const AUTOMATIC_FAILS = 4;
    const ARTIFICIAL_SUCCESS = 5;
    const ARTIFICIAL_FAILS = 6;
    const LOCK = 7;
    const ARTIFICIAL_FAILS_REFUSE = 8;
    /* 子状态名称 */
    const SUBSET_STATUS = [
        self::FINANCE_CHECK => '财务审核中',
        self::AUTOMATIC_PAYMENT => '自动出款中',
        self::AUTOMATIC_SUCCESS => '自动出款成功',
        self::AUTOMATIC_FAILS => '自动出款失败',
        self::ARTIFICIAL_SUCCESS => '人工出款成功',
        self::ARTIFICIAL_FAILS => '人工出款失败，取消',
        self::LOCK => '锁定',
        self::ARTIFICIAL_FAILS_REFUSE => '人工出款失败,拒绝',
    ];

    const ALIAS_PERFIX = 'sub-';

    /* 子状态别名 */
    const SUBSET_STATUS_ALIAS = [
        self::ALIAS_PERFIX . self::FINANCE_CHECK => self::FINANCE_CHECK,
        self::ALIAS_PERFIX . self::AUTOMATIC_PAYMENT => self::AUTOMATIC_PAYMENT,
        self::ALIAS_PERFIX . self::AUTOMATIC_SUCCESS => self::AUTOMATIC_SUCCESS,
        self::ALIAS_PERFIX . self::AUTOMATIC_FAILS => self::AUTOMATIC_FAILS,
        self::ALIAS_PERFIX . self::ARTIFICIAL_SUCCESS => self::ARTIFICIAL_SUCCESS,
        self::ALIAS_PERFIX . self::ARTIFICIAL_FAILS => self::ARTIFICIAL_FAILS,
        self::ALIAS_PERFIX . self::LOCK => self::LOCK,
        self::ALIAS_PERFIX . self::ARTIFICIAL_FAILS_REFUSE => self::ARTIFICIAL_FAILS_REFUSE,
    ];
    /* 财务待审核分类 */
    const FINANCE_WAIT = [
        self::FINANCE_CHECK => '财务审核中',
        self::AUTOMATIC_PAYMENT => '自动出款中',
        self::LOCK => '锁定'
    ];

    /* 汇款成功分类 */
    const PAYMENT_SUCCESS = [
        self::AUTOMATIC_SUCCESS => '自动出款成功',
        self::ARTIFICIAL_SUCCESS => '人工出款成功',
    ];
    /* 汇款失败分类 */
    const PAYMENT_FAILS = [
        self::AUTOMATIC_FAILS => '自动出款失败',
        self::ARTIFICIAL_FAILS => '人工出款失败，取消',
        self::ARTIFICIAL_FAILS_REFUSE => '人工出款失败，拒绝',
    ];

    /* 单条保存 */
    public static function saveOne($order_id)
    {
        //编辑
        $model = new self();
        $model->order_no = 'tx' . time() . rand(1000, 9999);
        $model->order_id = $order_id;
        $model->third_order_no = '';
        $model->withdrawal_status = self::FINANCE_CHECK;
        $model->lock_id = 0;
        return $model->save();
    }

    /*子状态文字*/
    public function getSubsetStatusTextAttribute()
    {
        if ($this->withdrawal_status==WithdrawalOrder::CHECK_PASSED && $this->lock_id > 0) {
            return self::SUBSET_STATUS[self::LOCK];
        }
        return isset(self::SUBSET_STATUS[$this->withdrawal_status]) ? self::SUBSET_STATUS[$this->withdrawal_status] : '';
    }

}
