<?php

use Illuminate\Database\Seeder;

class GameHelpsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('game_helps')->truncate();
        DB::table('game_helps')->insert([
            [
                //'id'                => 1,
                'kindid'            => 140,
                'kindname'          => '森林舞会',
                'title'             => '森林舞会帮助',
                'type'              => 1,
                'imageurl'          => 'http://192.168.0.146/ld/public/uploads/images/wanfa.png',
                'created_at'        => date('Y-m-d H:i:s', time()),
                'updated_at'        => date('Y-m-d H:i:s', time()),
            ],
            [
                //'id'                => 2,
                'kindid'            => 140,
                'kindname'          => '森林舞会',
                'title'             => '森林舞会倍数说明',
                'type'              => 2,
                'imageurl'          => 'http://192.168.0.146/ld/public/uploads/images/beishu.png',
                'created_at'        => date('Y-m-d H:i:s', time()),
                'updated_at'        => date('Y-m-d H:i:s', time()),
            ],
        ]);
    }
}
