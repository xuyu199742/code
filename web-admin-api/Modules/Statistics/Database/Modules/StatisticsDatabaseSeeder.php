<?php

namespace Modules\..\Modules\Statistics\Database\Seeders\..\Modules;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ../Modules/StatisticsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");
    }
}
