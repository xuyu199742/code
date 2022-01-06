<?php
/*稽核打码信息表*/
namespace Models\Treasure;

class UserAuditBetInfo extends Base
{
    protected $table = 'UserAuditBetInfo';
    protected $primaryKey  = 'UserID';

    public  $timestamps = false;

    public  $guarded = [];

    //增加稽核打码量
    public static function addScore($score,$local,$coins){
        try{
            $plus = $local  + $score->InsureScore;
            $AuditBet = self::where('UserID',$score->UserID)->first();
            if($AuditBet){
                $AuditBetScore  = $plus > $AuditBet->AuditBetScore ? bcadd($AuditBet->AuditBetScore , $coins) : bcadd($plus , $coins);
                self::where('UserID',$score->UserID)->update(['AuditBetScore' => $AuditBetScore]);
            }else{
                $AuditBetScore = bcadd($plus , $coins);
                self::create(['UserID' => $score->UserID,'AuditBetScore' => $AuditBetScore]);
            }
            \Log::channel('gold_change')->info($score->UserID . '当前稽核'.$AuditBetScore);
        }catch (\Exception $e){
            \Log::error(config('set.auditBet').'量:'.$e->getMessage());
        }
    }
}
