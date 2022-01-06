<?php

namespace Models\Platform;

use Models\BaseModel;

class Base extends BaseModel
{
    //数据库连接配置
    protected $connection = 'platform';
    public    $timestamps = false;
}
