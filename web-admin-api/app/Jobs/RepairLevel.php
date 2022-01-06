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

class RepairLevel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries   = 2;
    public $timeout = 30;


    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        try{
            AccountsInfo::select('UserID')->where('IsAndroid',0)->where('vip_exp','>',0)->chunk(100,function ($users){
                foreach ($users as $user){
                    AccountsInfo::addExp($user->UserID,0,false);
                }
                sleep(1);
            });
        }catch (\Exception $e){
            \Log::channel('queue')->info('修复所有用户VIP等级失败',['error' => $e->getMessage()]);
        }
    }
}
