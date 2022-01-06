<?php

return [
    //二维码h5下载地址
    'h5_download_url'  => 'http://192.168.0.200:8125',
    //二维码app下载地址
    'app_download_url' => 'http://192.168.0.100:9009',
    //控制端地址
    'control_site_url' => env('CONTROL_SITE_URL', 'http://192.168.0.100:8421'),
    //平台服务api地址
    'outer_platform_api' => env('OUTER_PLATFORM_API','http://127.0.0.1:8000'),
    //web-app api地址
    'web_app_api' => env('WEB_APP_API','http://127.0.0.1:801/api'),

    'locked' => ['control_site_url']
];
