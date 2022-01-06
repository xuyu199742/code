<?php


namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class QueryListener
{

    public function handle(QueryExecuted $event)
    {
//        $sql = str_replace("?", "'%s'", $event->sql);
//        if ($event->bindings) {
//            $sql = vsprintf($sql, $event->bindings);
//        }
//        if ($event->time > 1500) {
//            Log::channel('query_listener')->info($sql.' time: '.$event->time.'ms');
//        }
    }
}
