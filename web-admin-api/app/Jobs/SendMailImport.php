<?php

namespace App\Jobs;

use App\Imports\EmailToPlayersImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\EntityNotFoundException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Models\Accounts\AccountsInfo;
use Models\Treasure\GameMailInfo;

class SendMailImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries   = 2;
    public $timeout = 30;
    private $request;
    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request,$data)
    {
        $this->request=$request;
        $this->data=$data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            foreach ($this->request as $k => $v){
                $res = \DB::table(GameMailInfo::tableName())->insert($v);
            }
            //发送通知
            foreach ($this->data as $k=>$v) {
                eamilInform(intval($v[0]));
            }
        }catch (\Exception $e){
            return false;
        }
    }
}
