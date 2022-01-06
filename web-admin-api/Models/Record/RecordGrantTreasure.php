<?php
/*后台金币赠送记录表*/
namespace Models\Record;
/**
 * RecordID：    记录标识
 * MasterID：    管理员标识
 * ClientIP：    赠予地址
 * CollectDate： 操作日期
 * UserID：      用户标识
 * CurGold：     赠送前用户金币值
 * AddGold：     增加金币
 * Reason：      备注信息（赠送原因）
 */
class RecordGrantTreasure extends Base
{
    protected $table      = 'RecordGrantTreasure';
    protected $primaryKey = 'RecordID';

    /**
     * 添加记录
     *
     * @param int $UserID   用户id
     * @param int $CurGold  赠送前用户金币值
     * @param int $AddGold  增加金币
     * @param int $Reason   赠送原因
     * @param int $MasterID 管理员标识
     */
    public static function add($UserID, $CurGold, $AddGold, $Reason, $MasterID = 0)
    {
        $model              = new self();
        $model->MasterID    = $MasterID;
        $model->ClientIP    = request()->getClientIp() ?? '0.0.0.0';
        $model->CollectDate = date('Y-m-d H:i:s', time());
        $model->UserID      = $UserID;
        $model->CurGold     = $CurGold;
        $model->AddGold     = $AddGold;
        $model->Reason      = $Reason;
        return $model->save();
    }
}
