<?php

namespace Modules\Client\Http\Controllers;

use App\Exceptions\NewException;
use App\Http\Controllers\Controller;
use App\Traits\Sms;
use Matrix\Exception;
use Models\Accounts\AccountsInfo;
use Models\Accounts\MembersHandsel;
use Models\Accounts\MembersHandselLogs;
use Models\Accounts\MembersInfo;
use Models\AdminPlatform\SystemNotice;
use Models\AdminPlatform\Dict;
use Models\AdminPlatform\Version;
use Models\Treasure\RecordGameScore;
use Modules\Client\Transformers\VersionTransformer;

class ClientController extends Controller
{
    use Sms;

    public function index($appid){
        $version= Version::where('version_id',$appid)->first();
        if($version){
            return ResponeSuccess('查询成功',new VersionTransformer($version));
        }
        return ResponeFails('版本不存在');
    }

    public function getActivityCenter($type){
        try {
            if($type == 'mobile') $type = 'h5';
            if(!in_array($type,['u3d','h5'])){
                return ResponeFails('参数有误');
            }
            $data = [];
            foreach (Dict::DICT_PID as $key=>$val){
                if(in_array($key,[1,2,3])) {
                    $tmp = $val;
                    $tmp['id'] = $key;
                    switch ($key) {
                        case 1: //精彩活动
                            $tmp['children'] = Dict::GetEffective($type); //获取有效分类
                            break;
                        case 3: //游戏公告
                            $tmp['children'] = SystemNotice::GetList($type); //获取公告名称
                            break;
                        default:
                            $tmp['children'] = [];
                    }
                    $data[] = $tmp;
                }
            }
            return ResponeSuccess('Success', $data);
        } catch (Exception $e) {
            return ResponeFails('操作有误');
        }
    }

    public function getVipInfo($user_id) {
        try {
            $member_order = AccountsInfo::where("UserID", $user_id)->value("MemberOrder");
            if (!$member_order) {
                throw new NewException("玩家不存在！");
            }

            $members_info = MembersInfo::select('MemberOrder','LowerLimit','Status','ExtraIncomeRate','IsProfit','IsLoss','IsTask','IsWater','IsPour','RelationStatus')->get()->toArray();
            $members_handsel = MembersHandsel::select('MembersID','HandselType','HandselDays','HandselCoins')->get()->toArray();

            $vipExp = RecordGameScore::where('UserID',$user_id)->sum('JettonScore');

            $vip_logs = MembersHandselLogs::where([
                    "UserID" => $user_id,
                    "HandselType" => 4
                ])
                ->select('MembersID','HandselType')
                ->get()->toArray();

            $allCoins = 0;
            foreach($members_info as $key=>&$info) {
                $info['DayCoins'] = 0;
                $info['DayCount'] = 1;
                $info['WeekCoins'] = 0;
                $info['MonthCoins'] = 0;
                $info['VipCoins'] = 0;

                $info['VipIsOpen'] = 0;
                $info['DayIsOpen'] = 0;
                $info['WeekIsOpen'] = 0;
                $info['MonthIsOpen'] = 0;

                $info['VipExp'] = $vipExp;

                foreach ($members_handsel as $hand) {
                    if($info['MemberOrder'] == $hand['MembersID']) {
                        if($hand['HandselType'] == 1) {
                            $info['DayCoins'] = $hand['HandselCoins'];
                            $info['DayCount'] = $hand['HandselDays'];
                            $info['DayIsOpen']++;
                        }
                        if($hand['HandselType'] == 2) {
                            $info['WeekCoins'] = $hand['HandselCoins'];
                            $info['WeekIsOpen']++;
                        }
                        if($hand['HandselType'] == 3) {
                            $info['MonthCoins'] = $hand['HandselCoins'];
                            $info['MonthIsOpen']++;
                        }
                        if($hand['HandselType'] == 4) {
                            $info['VipCoins'] = $hand['HandselCoins'];
                            $info['VipIsOpen']++;
                            $allCoins += $hand['HandselCoins'];
                        }
                    }
                }

                $info['AllCions'] = $allCoins;

                $info['bReceive'] = 1; // h5说没用了
                $info['DayReceive'] = 0;
                $info['WeekReceive'] = 0;
                $info['MonthReceive'] = 0;
                $info['VipReceive'] = 0; // 0 未领取

                if($info['MemberOrder'] == $member_order) {
                    $day = date('Y-m-d 00:00:00', time() - ($info['DayCount']-1) * 86400);
                    $day_logs = MembersHandselLogs::where([
                            ["UserID", $user_id],
                            ["HandselType", 3],
                            ['CreatedTime', '>', $day]
                        ])
                        ->count();
                    if($day_logs > 0) $info['DayReceive'] = $day_logs;

                    $week = date('Y-m-d 00:00:00', strtotime('this week'));
                    $week_logs = MembersHandselLogs::where([
                            ["UserID", $user_id],
                            ["HandselType", 2],
                            ['CreatedTime' , '>', $week]
                        ])
                        ->count();
                    if($week_logs > 0) $info['WeekReceive'] = $week_logs;

                    $month = date('Y-m-01 00:00:00');
                    $month_logs = MembersHandselLogs::where([
                            ["UserID", $user_id],
                            ["HandselType", 3],
                            ['CreatedTime', '>', $month]
                        ])
                        ->count();
                    if($month_logs > 0) $info['MonthReceive'] = $month_logs;

                    if($day_logs > 0 && $day_logs > 0 && $day_logs > 0) $info['bReceive'] = 0;
                }

                foreach ($vip_logs as $vip) {
                    if ($info['MemberOrder'] == $vip['MembersID']) {
                        if ($vip['HandselType'] == 4) {
                            $info['VipReceive']++;
                        }
                    }
                }
            }
            return ResponeSuccess('Success', ['nVIPCount' => count($members_info),'nBetScore' => $members_info]);
        } catch (NewException $e) {
            \Log::info("[Client/getVipInfo]".$e);
            return ResponeFails($e->getMessage());
        }
    }
}
