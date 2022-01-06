<?php

use Illuminate\Database\Seeder;

class AgentRateConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('agent')->table('agent_rate_config')->truncate();
        DB::connection('agent')->table('agent_rate_config')->insert([
                [
                    'name'           => '实习生',
                    'water_min'      => 1,
                    'rebate'         => 50,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '市场专员',
                    'water_min'      => 10000,
                    'rebate'         => 60,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '代理',
                    'water_min'      => 50000,
                    'rebate'         => 70,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '总代理',
                    'water_min'      => 100000,
                    'rebate'         => 80,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '区域代理',
                    'water_min'      => 300000,
                    'rebate'         => 90,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '助理',
                    'water_min'      => 600000,
                    'rebate'         => 100,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '总助理',
                    'water_min'      => 1000000,
                    'rebate'         => 120,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '监事',
                    'water_min'      => 2000000,
                    'rebate'         => 140,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '总监',
                    'water_min'      => 4000000,
                    'rebate'         => 160,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '元老',
                    'water_min'      => 6000000,
                    'rebate'         => 180,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '股东',
                    'water_min'      => 8000000,
                    'rebate'         => 200,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '董事',
                    'water_min'      => 10000000,
                    'rebate'         => 220,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '董事长',
                    'water_min'      => 20000000,
                    'rebate'         => 225,
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
            ]
        );
    }
}
