<?php

namespace Models\Accounts;

class UserLevel extends Base
{
    public $table = 'UserLevel';

    protected $primaryKey = 'ID';

    public $guarded = [];

    const WITHDRAWAL = 'Withdrawal';
    const PROXY = 'Proxy';

    const FUNCTIONAL_TYPE = [
        self::WITHDRAWAL => '提现',
        self::PROXY => '代理'
    ];
}
