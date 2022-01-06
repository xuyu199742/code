<?php

namespace Models\Treasure;

use Models\BaseModel;

class Base extends BaseModel
{
    //数据库连接配置
    protected $connection = 'treasure';
    public    $timestamps = false;
}
