<?php

namespace Models\OuterPlatform;

class WashCodeHistory extends Base
{
    protected $table = 'wash_code_history';
    public $timestamps = false;

    public $guarded = [];

    public function records()
    {
        return $this->hasMany(WashCodeRecord::class);
    }
}
