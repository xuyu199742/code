<?php
/*签到物品配置*/
namespace Models\Platform;
use Models\AdminPlatform\SystemLog;

/**
 * GoodsID            int         物品标识
 * PackageID          int         礼包标识
 * TypeID             tinyint     物品类型（0、游戏币  1、钻石  2、道具  3、奖券）  （已取消）
 * PropertyID         int         道具标识
 * GoodsNum           bigint      物品数量（改为金币数量）
 * ResourceURL        nvarchar    物品图片（已取消）
 * CollectDate        datetime    配置时间
 */
class GamePackageGoods extends Base
{
    //数据表
    protected $table = 'GamePackageGoods';
    protected $primaryKey = 'GoodsID';
    /*物品类型*/
    const LQB       = 0;
    const DIAMOND   = 1;
    const PROP      = 2;
    const LOTTERIES = 3;
    const TYPE_ID   = [
        self::LQB           => '游戏币',
        self::DIAMOND       => '钻石',
        self::PROP          => '道具',
        self::LOTTERIES     => '奖券',
    ];
    public function getTypeIDTextAttribute()
    {
        return self::TYPE_ID[$this->TypeID] ?? '';
    }

    /*关联签到礼包*/
    public function package()
    {
        return $this->belongsTo(GamePackage::class,'PackageID','PackageID');
    }

    /*单条保存*/
    public static function saveOne($GoodsID = null)
    {
        $info = request()->all();
        //if ($GoodsID){
            //编辑
            $model              = self::find($GoodsID);
            if (!$model) {
                return false;
            }
       /* }else{
            //新增
            $model               = new self();
        }*/
        //$model->PackageID   = $info['PackageID'];
        //$model->TypeID      = $info['TypeID'] ?? self::LQB;
        //$model->PropertyID  = $info['PropertyID'] ?? 0;
        $model->GoodsNum    = $info['GoodsNum'] ? $info['GoodsNum'] * getGoldBase() : 0;
        //$model->ResourceURL = $info['ResourceURL'] ?? '';
        $model->CollectDate = date('Y-m-d H:i:s', time());
        return $model->save();
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改签到物品配置，签到物品标识为：'.$model->GoodsID);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除签到物品配置，签到物品标识为：'.$model->GoodsID);
        });
    }
}
