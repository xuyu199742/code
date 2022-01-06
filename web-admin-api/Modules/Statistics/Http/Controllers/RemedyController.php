<?php
namespace Modules\Statistics\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Models\Accounts\AccountsInfo;
use Models\Record\RecordUserLogon;

class RemedyController extends Controller
{
    /**
     * 留存率补救（计算某段日期内的留存率）
     *
     */
    public function retention()
    {
        //根据指定时间段查询，默认查询当天的注册人数
        $start_date = '2019-06-28';
        $end_date='2019-07-01'; //所要补救的时间段
        //根据指定时间查询用户注册数，并得到当天的注册用户id
        $user_reg = AccountsInfo::select('UserID',DB::raw("format(RegisterDate,'yyyy-MM-dd') as RegisterDate"))
            ->where('IsAndroid', 0)
            ->where('RegisterDate', '>=', $start_date.' 00:00:00')   //开始时间
            ->where('RegisterDate', '<=', $end_date.' 23:59:59')//结束时间
            ->get();
        $arr_reg = [];
        foreach ($user_reg as $k => $v){
            @$arr_reg[$v['RegisterDate']] = trim(@$arr_reg[$v['RegisterDate']].','.$v['UserID'],',');
        }
        //return $arr_reg ;
        //注册人数
        /*{
            "2019-06-28": "10040,10041,10042,10043",
            "2019-06-29": "10044",
            "2019-07-01": "11043,11044,11045"
        }*/
        //次日留存的登录日期的登录人数
        $next_start_date=date("Y-m-d", strtotime("+1 days", strtotime($start_date)));
        $next_end_date=date("Y-m-d", strtotime("+1 days", strtotime($end_date)));
        $next_user_logon=RecordUserLogon::select('UserID','CreateDate')
            ->where('CreateDate', '>=', $next_start_date.' 00:00:00')   //开始时间
            ->where('CreateDate', '<=', $next_end_date.' 23:59:59')//结束时间
            ->get();
       // return $next_user_logon;
        $next_arr_logon = [];
        foreach ($next_user_logon as $k => $v){
            @$next_arr_logon[$v['CreateDate']] = trim(@$next_arr_logon[$v['CreateDate']].','.$v['UserID'],',');
        }
        //登录人数
       /* {
            "2019-06-29": "10001,10040,10044",
            "2019-06-30": "10040",
            "2019-07-01": "10044,11043,11044",
            "2019-07-02": "11046,11048"
        }*/
       // return $next_arr_logon;
        //七日留存的登录日期的登录人数
        $seven_start_date=date("Y-m-d", strtotime("+7 days", strtotime($start_date)));
        $seven_end_date=date("Y-m-d", strtotime("+7 days", strtotime($end_date)));
        $seven_user_logon=RecordUserLogon::select('UserID','CreateDate')
            ->where('CreateDate', '>=', $seven_start_date.' 00:00:00')      //开始时间
            ->where('CreateDate', '<=', $seven_start_date.' 23:59:59')      //结束时间
            ->get();
        // return $user_logon;
        $seven_arr_logon = [];
        foreach ($seven_user_logon as $k => $v){
            @$seven_arr_logon[$v['CreateDate']] = trim(@$seven_arr_logon[$v['CreateDate']].','.$v['UserID'],',');
        }
        //return $seven_arr_logon;


        //30日留存的登录日期的登录人数
        $thirty_start_date=date("Y-m-d", strtotime("+30 days", strtotime($start_date)));
        $thirty_end_date=date("Y-m-d", strtotime("+30 days", strtotime($end_date)));
        $thirty_user_logon=RecordUserLogon::select('UserID','CreateDate')
            ->where('CreateDate', '>=', $seven_start_date.' 00:00:00')      //开始时间
            ->where('CreateDate', '<=', $seven_start_date.' 23:59:59')      //结束时间
            ->get();
        // return $user_logon;
        $thirty_arr_logon = [];
        foreach ($thirty_user_logon as $k => $v){
            @$thirty_arr_logon[$v['CreateDate']] = trim(@$thirty_arr_logon[$v['CreateDate']].','.$v['UserID'],',');
        }
       // return $thirty_arr_logon;

    }
}
