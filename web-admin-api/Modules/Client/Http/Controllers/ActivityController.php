<?php

namespace Modules\Client\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Models\Accounts\SystemStatusInfo;
use Models\Activity\ActivitiesNormal;
use Models\Activity\FirstChargeSignInLog;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\UserAuditBetInfo;

class ActivityController extends Controller
{
	//首充签到领取
    public function firstChargeSignInReceive($user_id)
    {
        //活动未配置
        $ActivitiesNormal = ActivitiesNormal::where('id',1)->first();
        if (empty($ActivitiesNormal)){
            return ResponeFails('暂无该活动');
        }
        //是否存在领取资格
        $FirstChargeSignInLog = FirstChargeSignInLog::where('user_id',$user_id)->first();
        if (!empty($FirstChargeSignInLog)){
            //是否超过领取时间
            $content    = json_decode($ActivitiesNormal->content,true);
            $day        = (new Carbon())->diffInDays(date('Y-m-d',strtotime($FirstChargeSignInLog->created_at)), true);
            if (!isset($content['days']) || $day >= $content['days']){
                return ResponeFails('已超过活动领取时间');
            }
            //活动最低充值金额是否满足
            $min_money  = min(array_column($content['detail'],'money'));
            if ($FirstChargeSignInLog->score < $min_money){
                return ResponeFails('不满足领取条件');
            }
            //是否已经领取过
            $RecordTreasureSerial = RecordTreasureSerial::where('UserID',$user_id)
                ->whereDate('CollectDate',date('Y-m-d'))
                ->where('TypeID',RecordTreasureSerial::FIRST_CHARGE_SIGNIN)
                ->first();
            if (!empty($RecordTreasureSerial)){
                return ResponeFails('您今天已经领取过了');
            }
        }else{
            return ResponeFails('不满足领取条件');
        }

        //领取记录
        $arr = array_column($content['detail'],'score','money');
        ksort($arr);
        $curscore   = 0;
        foreach ($arr as $k => $v){
            if ($k > $FirstChargeSignInLog->score){
                break;
            }else{
                $curscore = $v;
            }
        }

        $AuditBetScoreTake = SystemStatusInfo::where('StatusName','AuditBetScoreTake')->value('StatusValue');
        $audit_bet      = intval($curscore * $AuditBetScoreTake / 100);
        $db_treasure    = DB::connection('treasure');
        $db_record      = DB::connection('treasure');
        $db_treasure->beginTransaction();
        $db_record->beginTransaction();
        try {
            $gameScoreInfo = GameScoreInfo::where('UserID',$user_id)->lockForUpdate()->first();
            GameScoreInfo::where('UserID',$user_id)->increment('Score', $curscore);
            RecordTreasureSerial::addRecord($user_id,$gameScoreInfo->Score,$gameScoreInfo->InsureScore,$curscore,RecordTreasureSerial::FIRST_CHARGE_SIGNIN,0,'首充签到','',$audit_bet);
            $UserAuditBetInfo = UserAuditBetInfo::where('UserID', $user_id)->first();
            if($UserAuditBetInfo){
                $UserAuditBetInfo->AuditBetScore = $UserAuditBetInfo->AuditBetScore + $audit_bet;
            }else{
                $UserAuditBetInfo = new UserAuditBetInfo();
                $UserAuditBetInfo->AuditBetScore = $audit_bet;
            }
            $UserAuditBetInfo -> UserID = $user_id;
            $UserAuditBetInfo->save();
            $after_score = $gameScoreInfo->Score + $curscore;
            $db_treasure->commit();
            $db_record->commit();
            giveInform($user_id, $after_score, $curscore);
            return ResponeSuccess('领取成功');
        }catch (\Exception $e){
            $db_treasure->rollback();
            $db_record->rollback();
            return ResponeFails('领取失败');
        }

    }

}
