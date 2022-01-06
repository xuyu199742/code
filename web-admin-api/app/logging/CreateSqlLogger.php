<?php


namespace App\logging;
use Monolog\Logger;
use Logger\Monolog\Handler\MysqlHandler;

class CreateSqlLogger
{
    public function __invoke(array $config)
    {
        $channel = $config['name'] ?? env('APP_ENV');
        $monolog = new Logger($channel);
        $monolog->pushHandler(new MysqlHandler());
        return $monolog;
    }

}