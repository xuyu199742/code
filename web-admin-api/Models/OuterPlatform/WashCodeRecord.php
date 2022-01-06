<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2020/3/27
 * Time: 14:55
 */

namespace Models\OuterPlatform;


class WashCodeRecord extends Base
{
    public $table = 'wash_code_record';

    public $guarded = [];

    const UPDATED_AT = null;

    public function platform()
    {
        return $this->belongsTo(OuterPlatform::class, 'platform_id');
    }

    public function game()
    {
        return $this->belongsTo(OuterPlatformGame::class, 'kind_id', 'kind_id');
    }

    public function category()
    {
        return $this->belongsTo(GameCategory::class, 'category_id');
    }
}
