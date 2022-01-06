<?php
/*游戏内保险箱操作记录*/
namespace Models\Treasure;
/**
 * RecordID：主键标识
 * KindID：操作所在游戏标识
 * ServerID：操作所在房间标识
 * SourceUserID：操作用户标识
 * SourceGold：操作用户操作前金币
 * SourceBank：操作用户操作前保险箱金币
 * TargetUserID：接收用户
 * TargetGold：接收用户接收前金币
 * TargetBank：接收用户接收前保险箱金币
 * SwapScore：交易金额
 * Revenue：交易税收
 * IsGamePlaza：交易场所（0:大厅,1:网页）
 * TradeType：交易类型（1为存，2为取，3为转账）
 * ClientIP：操作IP
 * CollectDate：操作日期
 * CollectNote：备注信息
 */
use Models\Accounts\AccountsInfo;

class RecordInsure extends Base
{
    protected $table = 'RecordInsure';
    protected $primaryKey = 'RecordID';

    const DEPOSIT = 1;
    const WITHDRAWAL     = 2;
    const TRANSFER      = 3;
    const TRADE_TYPE         = [
        self::DEPOSIT => '存款',
        self::WITHDRAWAL => '取款',
        self::TRANSFER => '转账',
    ];

    /*转账用户*/
    public function transfer()
    {
        return $this->belongsTo(AccountsInfo::class,'SourceUserID','UserID');
    }

    /*收款用户*/
    public function receiver()
    {
        return $this->belongsTo(AccountsInfo::class,'TargetUserID','UserID');
    }

}
