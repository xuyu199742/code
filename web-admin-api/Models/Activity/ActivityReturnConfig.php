<?php
/* 活动配置 */
namespace Models\Activity;

use Illuminate\Database\Eloquent\SoftDeletes;
class ActivityReturnConfig extends Base
{
    use SoftDeletes;
    //数据表
    protected $table        = 'ActivityReturnConfig';
    protected $primaryKey   = 'id';
    protected $dates        = ['deleted_at'];
    public    $timestamps   = false;

    const NULLITY_ON   = 0;
    const NULLITY_OFF  = 1;
    const NULLITY      = [
        self::NULLITY_ON  => '启用',
        self::NULLITY_OFF => '禁用',
    ];
    const WATER  = 1;//流水
    const BET    = 2;//投注
    const PROFIT = 3;//盈利
    const LOSS   = 4;//亏损
    const CATEGORY = [
        self::WATER     => '流水返利',
        self::BET       => '投注返利',
        self::PROFIT    => '盈利返利',
        self::LOSS      => '亏损返利',
    ];

    /*类型*/
    public function getCategoryTextAttribute()
    {
        return self::CATEGORY[$this->category] ?? '';
    }

    /*状态*/
    public function getNullityTextAttribute()
    {
        return self::NULLITY[$this->nullity ?? self::NULLITY_ON];
    }

    /*关联福利配置*/
    public function weal()
    {
        return $this->hasMany(ReturnRateConfig::class, 'activity_return_id', 'id');
    }

    /*关联活动记录*/
    public function logs()
    {
        return $this->hasMany(ReturnRecord::class, 'activity_return_id', 'id');
    }
}
