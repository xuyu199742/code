<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Models\Treasure\RecordGameScore;
use Models\AdminPlatform\StatisticsWinLose;
class StatisticsRealTimeWinOrLose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:win_lose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计实时输赢,粒度默认5分钟';

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

    public function handle(Schedule $schedule)
    {
        $schedule->call(function () {

            $minite = date('i');
            $minite = str_pad($minite - $minite % 5, 2, 0, STR_PAD_LEFT);
            $time = date("Y-m-d H:$minite:00");
            $time1 = '2019-06-25 00:00:00';
            $list = RecordGameScore::select([
                'ServerID as server_id',
                'KindID as kind_id',
                \DB::raw('SUM(ChangeScore) as change_score'),
                \DB::raw('SUM(JettonScore) as jetton_score'),
                \DB::raw('SUM(SystemScore) as system_score'),
                \DB::raw('SUM(SystemServiceScore) as system_service_score'),
            ])
                ->where('UpdateTime', '>=', $time1)//date('Y-m-d 00:00:00'))
                ->where('UpdateTime', '<=', $time1.'23:59:59')
                ->groupBy(['KindID', 'ServerID'])
                ->get();
            //dd($list);
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = $time;
            }

            $data = StatisticsWinLose::addAll($list);
        })
            ->everyFiveMinutes()//每五分            钟运行一次任务
            ->daily(); //每天
        //dd(AdminUser::all());

    }
}
