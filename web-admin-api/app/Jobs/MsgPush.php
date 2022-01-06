<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Models\Treasure\GameChatUserInfo;

class MsgPush implements ShouldQueue
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

    public function pushToAll(){
        $data = getPushStorage();
        $form_params = [
            'data' => [
                'officialPay' => intval($data['officialPay']),
                'thirdPay' => intval($data['thirdPay']),
                'withdraw' => intval($data['withdraw']),
                'email' => intval($data['email']),
            ],
            'to' => $data->to ?? '',
            'type' => $data->type ?? 'publish',
        ];
        try{
            $client = new \GuzzleHttp\Client();
            $client->request('POST', env('APP_URL').':2121', [
                'timeout' => 3,
                'form_params' => $form_params
            ]);
            return true;
        }catch (\Exception $exception){
            \Log::channel('push_fail')->info('消息推送失败:'.$exception->getMessage().PHP_EOL.json_encode($form_params));
            return false;
        }
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        try{
            $request = $this->request;
            $type = $request['type'] ?? '';
            if(!$type){
                return false;
            }
            //存储推送
            setPushStorage($type);
            $is_online = GameChatUserInfo::count();
            //有人在线即推送
            if($is_online){
                //开始推送
                $this->pushToAll();
            }
            return true;
        }catch (\Exception $e){
            return false;
        }

    }
}
