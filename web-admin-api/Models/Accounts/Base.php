<?php

namespace Models\Accounts;

use Models\BaseModel;
class Base extends BaseModel
{
    //数据库连接配置
    protected $connection = 'accounts';
    public    $timestamps = false;
}
