<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Models\Agent\ChannelInfo;
use Models\Treasure\GameMailInfoJob;


class SendMailChannel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 2;
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
     * @return void
     */
    public function handle()
    {
         $request = $this->request;
         $channel_id = $request['ChannelID'];
         $create = date('Y-m-d H:i:s', time());
         $start_time=$request['StartTime'] ?? $create;
         $end_time = date('Y-m-d H:i:s', strtotime("+7 day", strtotime($start_time)));
         \DB::table(GameMailInfoJob::tableName())->insert([
             'ChannelID' => $channel_id,
             'Title' => $request['Title'],
             'Context' => $request['Context'],
             'CreateTime' => $create,
             'StartTime' => $start_time,
             'EndTime' => $end_time,//有效期结束时间，7天内有效
             'admin_id' => $request['admin_id'] ??0,//操作人id
        ]);
        ChannelInfo::where('parent_id', $channel_id)->chunk(50, function ($items) use ($request, $create, $start_time, $end_time) {
            $data_channel = [];
            foreach ($items as $key => $item) {
                $data_channel[$key]['ChannelID'] = $item->channel_id;
                $data_channel[$key]['Title'] = $request['Title'];
                $data_channel[$key]['Context'] = $request['Context'];
                $data_channel[$key]['CreateTime'] = $create;
                $data_channel[$key]['StartTime'] = $start_time;
                $data_channel[$key]['EndTime'] = $end_time;//有效期结束时间，7天内有效
                $data_channel[$key]['admin_id'] = $request['admin_id'] ??0;//操作人id
            }
            \DB::table(GameMailInfoJob::tableName())->insert($data_channel);
        });
    }
}
