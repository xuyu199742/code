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

namespace Modules\Payment\Packages\ThirdPay;

use Illuminate\Support\ServiceProvider;


class PayServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        if (function_exists('config_path')) {
            $publishPath = config_path('payments.php');
        } else {
            $publishPath = base_path('config/payments.php');
        }
        $this->publishes([
            __DIR__ . '/config/payments.php' => $publishPath,
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/payments.php', 'payments');

        $this->app->singleton(Pay::class, function () {
            return new Pay();
        });
    }

    public function provides()
    {
        return [Pay::class];
    }
}
