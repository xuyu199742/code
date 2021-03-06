<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */

    'supportsCredentials'    => true,
    'allowedOrigins'         => explode(',', env('ALLOWEDORIGINS', '*')),
    'allowedOriginsPatterns' => [],
    'allowedHeaders'         => ['*'],
    'allowedMethods'         => ['*'],
    'exposedHeaders'         => ['Authorization'],
    'maxAge'                 => 0,

];
