<?php
/* 活动配置 */
namespace Models\Activity;

use Illuminate\Database\Eloquent\SoftDeletes;
class RankingConfig extends Base
{
    use SoftDeletes;
    protected $table = 'RankingConfig';
    protected $dates = ['deleted_at'];

    const BET_TYPE      = 1;
    const WATER_TYPE    = 2;
    const TYPE          = [
        self::BET_TYPE      => '下注',
        self::WATER_TYPE    => '流水',
    ];

    /*类型*/
    public function getTypeTextAttribute()
    {
        return self::TYPE[$this->type] ?? '';
    }
}
