<?php
/*转盘配置表*/

namespace Models\Activity;


use Carbon\Carbon;
use Models\AdminPlatform\SystemSetting;
use Models\Record\RecordTreasureSerial;

class ActivitiesNormal extends Base
{
    //正常描述性活动，没有附件
    protected $table = 'activities_normal';
    protected $primaryKey = 'id';
    protected $guarded = [];

    //转盘类型
    const FIRST_CHARGE = 1; // 首充签到
    const ANSWER_ACTIVITY = 2; //答题活动
    const BETTING_TURNTABLE = 3; //投注转盘
    const RECHARGE_TURNTABLE = 4; //充值转盘


    const PIDS = [
        self::FIRST_CHARGE,
        self::ANSWER_ACTIVITY,
        self::BETTING_TURNTABLE,
        self::RECHARGE_TURNTABLE,
    ];

    const BRONZE = 1;
    const SILVER = 2;
    const GOLD = 3;

    const RANK_LIST = [
        self::BRONZE => '青铜',
        self::SILVER => '白银',
        self::GOLD   => '黄金'
    ];

    // 单个活动集合
    const SINGLE_PIDS = [
        self::FIRST_CHARGE,
        self::ANSWER_ACTIVITY,
    ];

    public static function AnswerGiveConfig($status = true)
    {
        $data = [
            'answer_min'     => 0,
            'answer_max'     => 0,
            'show_min'       => 0,
            'show_max'       => 0,
            'interval'       => 0,
            'answer_num'     => 0,
            'status'         => 0,
            'answer_correct' => '',
        ];
        $act = ActivitiesNormal::query()
            ->where('pid', self::ANSWER_ACTIVITY)
            ->when($status,function ($query){
                $query->where('status', 1);
            })
            ->first();
        if ($act) {
            $content = json_decode($act['content'], true);
            $answer_correct = SystemSetting::where('group', 'prots')->where('key', 'app_download_url')->value('value');
            $parse_url = parse_url($answer_correct);
            $port = $parse_url['port'] ?? '';
            $url = ($parse_url['host'] ?? '') . ($port ? ':' . $port : '');
            $data = [
                'answer_min'     => realCoins($content['answer_min']),
                'answer_max'     => realCoins($content['answer_max']),
                'show_min'       => realCoins($content['show_min']),
                'show_max'       => realCoins($content['show_max']),
                'interval'       => $content['interval'],
                'answer_num'     => $content['answer_num'],
                'status'         => $act['status'],
                'answer_correct' => $url,
            ];
        }
        return $data;
    }

    public static function isGave($user, $config)
    {
        $carbon = Carbon::parse($user->RegisterDate)->startOfDay();
        $days = (new Carbon)->diffInDays($carbon, true);
        if(!$config['interval']){
            return false;
        }
        if ($days % $config['interval'] !== 0) {
            return false;
        }
        $count = RecordTreasureSerial::query()
            ->where('UserID', $user->UserID)
            ->where('TypeID', RecordTreasureSerial::ANSWER_GIVE_TYPE)
            ->count();
        $today = RecordTreasureSerial::query()
            ->where('UserID', $user->UserID)
            ->where('TypeID', RecordTreasureSerial::ANSWER_GIVE_TYPE)
            ->whereBetween('CollectDate', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
            ->count();
        if ($count >= $config['answer_num'] || $today) {
            return false;
        }
        return true;
    }
}
