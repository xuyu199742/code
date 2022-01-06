<?php

namespace Models\OuterPlatform;

class GameCategory extends Base
{
    protected $table = 'game_category';

    public $timestamps = false;

    public $guarded = [];

    const STATUS_ON   = 1;
    const STATUS_OFF  = 0;
    const STATUS      = [
        self::STATUS_ON  => '启用',
        self::STATUS_OFF => '禁用',
    ];

    const GAME_CATEGORY = 0;   //游戏分类

    const HOT_TAG = 1;   //热门标签

    const NO_CATEGORY_ID = 2;   //棋牌游戏对应的category_id

    const NO_CATEGORY = '棋牌游戏';    //特殊分类

    const HOT_CATEGORY = '热门游戏';   //客户端洗码游戏分类不包括热门游戏


    //定义热门字段对应的ID
    const HOT_SORT    = 1;
    const EHOT_SORT   = 6;
    const QHOT_SORT   = 7;

    //关联游戏
    public function relation(){
        return $this->hasMany(GameCategoryRelation::class,'category_id','id');
    }

}
