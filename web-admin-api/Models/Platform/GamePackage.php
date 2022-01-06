<?php
/*签到礼包配置*/
namespace Models\Platform;
use Models\AdminPlatform\SystemLog;

/**
 * PackageID            int             礼包标识
 * Name                 nvarchar        礼包名称
 * TypeID               tinyint         礼包类型（0、抽奖签到礼包  1、累计签到礼包）
 * SortID               int             排序标识（从大到小）
 * Nullity              tinyint         是否禁用（0、正常  1、禁用）
 * PlatformKind         int             平台编号0:全部 1:LUA,2:H5,3:U3D
 * Describe             nvarchar        礼包描述
 * CollectDate          datetime        配置时间
 */
class GamePackage extends Base
{
    //数据表
    protected $table = 'GamePackage';
    protected $primaryKey = 'PackageID';

    /*平台类型*/
    const PLATFORM_ALL  = 0;
    const PLATFORM_LUA  = 1;
    const PLATFORM_H5   = 2;
    const PLATFORM_U3D  = 3;
    const PLATFORM           = [
        self::PLATFORM_ALL      => '全部',
        self::PLATFORM_LUA      => 'LUA',
        self::PLATFORM_H5       => 'H5',
        self::PLATFORM_U3D      => 'U3D',
    ];
    public function getPlatformKindTextAttribute()
    {
        return self::PLATFORM[$this->PlatformKind] ?? '';
    }

    /*礼包类型*/
    const LUCKY_SIGN_IN     = 0;
    const TOTAL_SIGN_IN     = 1;
    const TYPE_ID           = [
        self::LUCKY_SIGN_IN  => '抽奖签到礼包',
        self::TOTAL_SIGN_IN     => '累计签到礼包',
    ];
    public function getTypeIDTextAttribute()
    {
        return self::TYPE_ID[$this->TypeID] ?? '';
    }

    /*状态类型*/
    const NULLITY_ON        = 0;
    const NULLITY_OFF       = 1;
    const NULLITY           = [
        self::NULLITY_ON    => '正常',
        self::NULLITY_OFF   => '禁用',
    ];
    public function getNullityTextAttribute()
    {
        return self::NULLITY[$this->Nullity] ?? '';
    }


    /*单条保存*/
    public static function saveOne($PackageID = null)
    {
        $info = request()->all();
        //if ($PackageID){
            //编辑
            $model              = self::find($PackageID);
            if (!$model) {
                return false;
            }
        /*}else{
            //新增
            $model               = new self();
        }*/
        $model->Name         = $info['Name'];
        $model->TypeID       = $info['TypeID'];
        //$model->SortID       = $info['SortID'] ?? 0;
        $model->Nullity      = $info['Nullity'] ?? self::NULLITY_ON;
        $model->PlatformKind = $info['PlatformKind'];
        $model->Describe     = $info['Describe'];
        $model->CollectDate  = date('Y-m-d H:i:s', time());
        return $model->save();
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改签到礼包配置，签到礼包标识为：'.$model->GoodsID);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除签到礼包配置，签到礼包标识为：'.$model->GoodsID);
        });
    }
}
