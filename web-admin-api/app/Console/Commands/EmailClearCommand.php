<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Models\Treasure\GameMailInfo;

class EmailClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '系统发件箱大于7天标记';

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
        $date = date('Y-m-d', strtotime("-7 day", time()));
        GameMailInfo::where('CreateTime', '<', $date)->update(['IsDelete' => 1]);
    }
}
