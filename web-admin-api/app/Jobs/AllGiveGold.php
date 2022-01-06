<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\EntityNotFoundException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Models\Accounts\AccountsInfo;
use Models\Treasure\GameMailInfo;

class AllGiveGold implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries   = 2;
    public $timeout = 30;
    private $request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;

    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        try{
            //发送通知
            foreach ($this->request as $k => $v){
                giveInform($v['user_id'], $v['curscore'], $v['add_gold'], 1);
            }
            return true;
        }catch (\Exception $e){
            return false;
        }

    }
}
