<?php
/*签到配置*/
namespace Models\Platform;
use Models\AdminPlatform\SystemLog;

/**
 * SignID           int             签到标识
 * TypeID           tinyint         签到类型（0、每日签到  1、累计签到）(已取消)
 * PackageID        int             签到获得礼包标识
 * Probability      int             签到抽奖获得礼包的概率（百分比）
 * NeedDay          int             累计签到所需天数
 * SortID           int             排序标识（从大到小）
 * Nullity          tinyint         是否禁用（0、正常  1、禁用）
 * CollectDate      datetime        配置时间
 */
class GameSignIn extends Base
{
    //数据表
    protected $table = 'GameSignIn';
    protected $primaryKey = 'SignID';

    /*签到类型*/
    const EVERYDAY_SIGN_IN = 0;
    const TOTAL_SIGN_IN    = 1;
    const TYPE_ID         = [
        self::EVERYDAY_SIGN_IN  => '每日签到',
        self::TOTAL_SIGN_IN     => '累计签到',
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

    /*关联签到礼包*/
    public function package()
    {
        return $this->belongsTo(GamePackage::class,'PackageID','PackageID');
    }

    /*单条保存*/
    public static function saveOne($SignID = null)
    {
        $info = request()->all();
        //if ($SignID){
            //编辑
            $model              = self::find($SignID);
            if (!$model) {
                return false;
            }
       /* }else{
            //新增
            $model               = new self();
        }*/
        //$model->TypeID      = $info['TypeID'] ?? 0;
        //$model->PackageID   = $info['PackageID'];
        $model->Probability = $info['Probability'];
        $model->NeedDay     = $info['NeedDay'] ?? 1;
        //$model->SortID      = $info['SortID'] ?? 0;
        //$model->Nullity     = $info['Nullity'] ?? self::NULLITY_ON;
        $model->CollectDate = date('Y-m-d H:i:s', time());
        return $model->save();
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改签到配置，签到标识为：'.$model->SignID);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除签到配置，签到标识为：'.$model->SignID);
        });
    }
}
