<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrontabCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '执行计划任务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $php_dir     = exec('which php');
        $artisan_dir = exec('pwd');
        $no_hup      = exec('which nohup');
        $command     = "\* \* \* \* \* $php_dir $artisan_dir/artisan schedule:run >> /dev/null 2>&1";
        $queue_pids=exec("ps -ef|grep queue|grep -v grep| awk '{print $2}'| xargs");
        if(!empty($queue_pids)){
	        exec("ps -ef|grep queue|grep -v grep|awk '{print $2}'| xargs kill -9");
        }
	    $queue_pids=exec("ps -ef|grep WorkerMan|grep -v grep|awk '{print $2}'| xargs");
	    if(!empty($queue_pids)){
		    exec("ps -ef|grep WorkerMan|grep -v grep|awk '{print $2}'| xargs kill -9");
	    }
        exec("$php_dir $artisan_dir/artisan queue:listen --queue=high,default,low --tries=5 --timeout=60 >> queue.log &"); //队列
        exec("$php_dir $artisan_dir/artisan queue:retry all>> requeue.log &"); //处理失败队列
        exec("$php_dir $artisan_dir/artisan socket:io start --daemonize >> push.log &"); //启动消息推送
        exec('sudo echo ' . $command . ' > cron.txt'); //定时脚本
        exec("crontab cron.txt");
    }
}
