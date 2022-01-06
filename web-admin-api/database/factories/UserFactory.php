<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Models\AdminPlatform\StatisticsWinLose;
use \Models\Platform\GameRoomInfo;
use Models\Treasure\RecordScoreDaily;
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});
//生产折线图统计数据
$factory->define(StatisticsWinLose::class, function (Faker $faker) {
    $game_room_info=GameRoomInfo::inRandomOrder()->first();
    $unix_time=$faker->dateTimeThisMonth()->getTimestamp();
    $minite = date('i',$unix_time);
    $minite = str_pad($minite - $minite % 5, 2, 0, STR_PAD_LEFT);
    $time = date("Y-m-d H:$minite:00",$unix_time);
    return [
        'server_id'=>$game_room_info->ServerID,
        'kind_id'=>$game_room_info->KindID,
        'change_score'=>$faker->numberBetween(-10000,10000),
        'jetton_score'=>$faker->numberBetween(-10000,10000),
        'system_score'=>$faker->numberBetween(-10000,10000),
        'system_service_score'=>$faker->numberBetween(-10000,10000),
        'create_time'=>$time,
    ];
});

//生产折线图统计数据
$factory->define(RecordScoreDaily::class, function (Faker $faker) {
    return [
        'UserID'=>10040,
        'DateID'=>0,
        'ChangeScore'=>$faker->numberBetween(-10000,10000),
        'JettonScore'=>$faker->numberBetween(-10000,10000),
        'SystemServiceScore'=>$faker->numberBetween(-10000,10000),
        'StreamScore'=>$faker->numberBetween(0,10000),
        'Status'=>0,
        'UpdateDate'=>date('Y-m-d',time()),
    ];
});