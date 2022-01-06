<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToArray;


class EmailToPlayersImport implements ToArray
{
    /**
    * @param Collection $collection
    */
    public function Array(Array $tables)
    {
        return $tables;
    }
    //批量导入50条
    public function batchSize(): int
    {
        return 100;
    }
    //以5条数据基准切割数据
    public function chunkSize(): int
    {
        return 100;
    }
}
