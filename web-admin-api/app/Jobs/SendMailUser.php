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

class SendMailUser implements ShouldQueue
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
        $this->request=$request;

    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        try{
            $request=$this->request;
            $game_ids=$request['GameIDs'];
            $user_ids = AccountsInfo::whereIN('GameID',$game_ids)->pluck('UserID');
            $count=count($user_ids);
            if($count<1){
                return false;
            }
            $data_user=[];
            foreach ($user_ids as $k=>$v)
            {
                $data_user[$k]['UserID']=$v;
                $data_user[$k]['Title']=$request['Title'];
                $data_user[$k]['Context']=$request['Context'];
                $data_user[$k]['CreateTime']=date('Y-m-d H:i:s',time());
                $data_user[$k]['admin_id']=$request['admin_id'] ?? 0;
                $data_user[$k]['receive_id']=$request['ID'] ?? 0;
            }
            $res = \DB::table(GameMailInfo::tableName())->insert($data_user);
            //发送通知
            foreach ($user_ids as $k=>$v)
            {
                eamilInform($v);
            }
            if(!$res){
               throw new EntityNotFoundException('false');
            }
            return true;
        }catch (\Exception $e){
            return false;
        }

    }
}
