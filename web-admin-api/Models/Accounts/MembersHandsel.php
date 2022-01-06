<?php

namespace Models\Accounts;


use Carbon\Carbon;

class MembersHandsel extends Base
{
    protected $table  = 'MembersHandsel';

    public  $guarded = [];

    //彩金类型
    const HandselType = [
        self::DAYS      => '日彩金',
        self::WEEK      => '周彩金',
        self::MONTH     => '月彩金',
        self::PROMOTION => '晋级彩金',
    ];

    const DAYS      = 1;
    const WEEK      = 2;
    const MONTH     = 3;
    const PROMOTION = 4;

    //获取彩金类型名称
    public static function getTypeName($m){
        return self::HandselType[$m->HandselType].($m->HandselType == 1 ? '('.$m->HandselDays.'天)' : '');
    }

    public static function getVipDiff($user)
    {
        $up = MembersInfo::where('MemberOrder', $user->MemberOrder)->value('UpperLimit') ?: 0;
        $next = MembersInfo::where('MemberOrder', '>', $user->MemberOrder)->orderBy('MemberOrder', 'asc')->first();
        $down = $next->UpperLimit ?? 0;
        $full = $down - $up;
        $part = ($user->vip_exp * realRatio()) - $up;
        $vip_info = [
            'scale' => 100,
            'cur_exp' => 0,
            'short_exp' => 0,
            'cur_level' => $user->MemberOrder,
            'next_level' => $user->MemberOrder,
            'promotion_handsel' => 0
        ];
        if ($full > $part) {
            $scale = bcdiv($part, $full, 2) * 100;
            $vip_info['scale'] = $scale > 0 ? $scale : 0;
            $vip_info['cur_exp'] = $part > 0 ? realCoins($part) : 0;
            $vip_info['short_exp'] = realCoins($full - $part);
            if ($next->id ?? '') {
                $vip_info['next_level'] = $next->MemberOrder;
                $vip_info['promotion_handsel'] = realCoins(self::query()
                        ->select('HandselCoins')
                        ->where('MembersID', $next->id)
                        ->where('HandselType', self::PROMOTION)
                        ->value('HandselCoins') ?? 0);
            }
        }
        return $vip_info;
    }

    public static function getVipConfig($user)
    {
        $list = MembersInfo::query()
            ->from(MembersInfo::tableName() . ' as a')
            ->select('id', 'MemberOrder', 'UpperLimit', 'ExtraIncomeRate')
            ->with(['membersHandsel:HandselID,MembersID,HandselCoins,HandselType'])
            ->where('Status', 1)
            ->get()
            ->transform(function ($model) use ($user) {
                $model->UpperLimit = realCoins($model->UpperLimit);
                $model->ExtraIncomeRate = $model->ExtraIncomeRate . '%';
                $golds = [self::WEEK => 'week', self::MONTH => 'month', self::PROMOTION => 'promotion'];
                $membersHandsel = $model->membersHandsel->toArray();
                foreach ($golds as $key => $gold) {
                    $mk = array_search($key, array_column($membersHandsel, 'HandselType'));
                    $mh = $mk !== false ? $membersHandsel[$mk] ?? [] : [];
                    $m = [
                        'HandselID' => $mh ? $mh['HandselID'] : 0,
                        'HandselCoins' => $mh ? realCoins($mh['HandselCoins']) : 0,
                    ];
                    if ($mh && $user) {
                        $m['HandselStatus'] = self::vipHandselStatus($model, $user, $mk);
                    }
                    $model[$gold] = $m;
                }
                unset($model->membersHandsel);
                return $model;
            });
        return $list;
    }

    public static function vipHandselStatus($member, $user, $key)
    {
        $status = 1;
        if ($member->MemberOrder ?? '') {
            switch (bccomp($member->MemberOrder, $user->MemberOrder)) {
                case -1:
                    switch ($member->membersHandsel[$key]->HandselType) {
                        case self::PROMOTION:
                            $status = 3;
                            if (!MembersHandselLogs::query()
                                ->where('UserID', $user->UserID)
                                ->where('HandselType', self::PROMOTION)
                                ->where('MembersID', $member->id)
                                ->exists()) {
                                $status = 2;
                            }
                            break;
                    }
                    break;
                case 0:
                    $status = 3;
                    switch ($member->membersHandsel[$key]->HandselType) {
                        case self::WEEK:
                            $startTime = Carbon::now()->subWeek(0)->startOfWeek()->toDateTimeString();
                            $endTime = Carbon::now()->subWeek(0)->endOfWeek()->toDateTimeString();
                            if (!MembersHandselLogs::query()
                                ->whereBetween('CreatedTime', [$startTime, $endTime])
                                ->where('UserID', $user->UserID)
                                ->where('HandselType', self::WEEK)
                                ->where('MembersID', $member->id)
                                ->exists()) {
                                $status = 2;
                            }
                            break;
                        case self::MONTH:
                            $startTime = Carbon::now()->subWeek(0)->startOfMonth()->toDateTimeString();
                            $endTime = Carbon::now()->subWeek(0)->endOfMonth()->toDateTimeString();
                            if (!MembersHandselLogs::query()
                                ->whereBetween('CreatedTime', [$startTime, $endTime])
                                ->where('UserID', $user->UserID)
                                ->where('HandselType', self::MONTH)
                                ->where('MembersID', $member->id)
                                ->exists()) {
                                $status = 2;
                            }
                            break;
                        case self::PROMOTION:
                            if (!MembersHandselLogs::query()
                                ->where('UserID', $user->UserID)
                                ->where('HandselType', self::PROMOTION)
                                ->where('MembersID', $member->id)
                                ->exists()) {
                                $status = 2;
                            }
                            break;
                    }
                    break;
                case 1:
                    $status = 4;
                    break;
            }
        }
        return $status;
    }
}
