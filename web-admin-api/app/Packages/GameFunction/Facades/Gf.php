<?php

namespace App\Packages\GameFunction\Facades;

use Illuminate\Support\Facades\Facade;

class Gf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Packages\GameFunction\Gf::class;
    }
}
