<?php
/*
 |--------------------------------------------------------------------------
 |
 |--------------------------------------------------------------------------
 | Notes:
 | Class PayServiceProvider
 | User: Administrator
 | Date: 2019/7/11
 | Time: 16:08
 |
 |  * @return
 |  |
 |
 */

namespace App\Packages\GameFunction;

use Illuminate\Support\ServiceProvider;


class GfServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        if (function_exists('config_path')) {
            $publishPath = config_path('game_map_function.php');
        } else {
            $publishPath = base_path('config/game_map_function.php');
        }
        $this->publishes([
            __DIR__ . '/config/game_map_function.php' => $publishPath,
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/game_map_function.php', 'game_map_function');

        $this->app->singleton(Gf::class, function () {
            return new Gf();
        });
    }

    public function provides()
    {
        return [Gf::class];
    }
}
