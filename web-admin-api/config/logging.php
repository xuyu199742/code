<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],
        'queue' => [
            'driver' => 'daily',
            'path' => storage_path('logs/queue.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],
        'sqllog' => [
            'driver' => 'custom',
            'via' => App\Logging\CreateSqlLogger::class,
        ],
        'callback'=>[
            'driver' => 'single',
            'path' => storage_path('logs/callback.log'),
        ],
        //四方支付请求日志
        'sifang_pay_send'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/sifang/pay_send.log'),
        ],
        //四方支付回调日志
        'sifang_pay_callback'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/sifang/pay_callback.log'),
        ],
        //四方代付支付回调日志
        'daifu_callback'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/sifang/daifu_callback.log'),
        ],
        //四方代付支付请求日志
        'daifu_send'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/sifang/daifu_send.log'),
        ],
        'push_success'=>[
            'driver' => 'single',
            'path' => storage_path('logs/notification/success.log'),
        ],
        'push_fail'=>[
            'driver' => 'single',
            'path' => storage_path('logs/notification/fail.log'),
        ],
        //金币变化日志日志
        'gold_change'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/goldchange/success.log'),
        ],
        'outer_platform'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/threegame/deduction.log'),
        ],
        //外接平台拉单日志
        'outer_game_record'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/threegame/game_record.log'),
        ],
        //外接平台手动下分日志
        'outer_platform_quit'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/threegame/manual_quit.log'),
        ],
        //外接平台登录日志
        'outer_platform_login'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/threegame/login.log'),
        ],
        'query_listener'=>[
            'driver' => 'daily',
            'path' => storage_path('logs/queryListener/query.log'),
        ],
    ],

];
