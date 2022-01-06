<?php

namespace App\Packages\GameFunction;


use Models\Treasure\RecordDrawScore;

interface formatInterface
{
    public function format(RecordDrawScore $recordDrawScore);

}
