<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Models\Treasure\GameMailInfoJob;


class AllUserInform implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;//任务可以尝试的最大次数
    public $timeout = 30;//任务可以执行的最大秒数 (超时时间)
    public $config = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        try{
            return GameMailInfoJob::saveOne($this->config);
        }catch (\Exception $exception){
            return false;
        }
    }
}
