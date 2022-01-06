<?php
/*签到记录*/
namespace Models\Record;
/**
 * RecordID         int             记录标识
 * UserID           int             用户标识
 * SignType         tinyint         签到类型（0、每日签到  1、累计签到）
 * PackageName      nvarchar        获取礼包名称
 * PackageGoods     nvarchar        礼包详情
 * Probability      int             签到抽奖获得礼包的概率（百分比）
 * NeedDay          int             累计签到所需天数
 * TotalDay         int             累计签到天数
 * ClinetIP         nvarchar        记录地址
 * CollectDate      datetime        记录时间
 *
**/
use Models\Accounts\AccountsInfo;
class RecordGameSignIn extends Base
{
    protected $table='RecordGameSignIn';
    const EVERYDAY_SIGN_IN = 0;
    const TOTAL_SIGN_IN    = 1;
    const SIGN_TYPE         = [
        self::EVERYDAY_SIGN_IN  => '每日签到',
        self::TOTAL_SIGN_IN     => '累计签到',
    ];

    /*签到类型*/
    public function getSignTypeTextAttribute()
    {
        return self::SIGN_TYPE[$this->SignType] ?? '';
    }

    /*关联用户*/
    public function account()
    {
        return $this->belongsTo(AccountsInfo::class,'UserID','UserID');
    }
}
