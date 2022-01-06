<?php

namespace Models\Record;

use Models\BaseModel;

class Base extends BaseModel
{
    //数据库连接配置
    protected $connection = 'record';
    public    $timestamps = false;
}
