<?php
namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\Record\RecordUserLogon;
use Validator;

class PaymentOverviewController extends Controller
{
    /**
     * 支付概况
     *
     */
    public function paymentList(Request $request)
    {
        Validator::make(request()->all(), [
            'year'       => ['nullable', 'numeric'], //年
            'month'      => ['nullable', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],//月
        ], [
            'year.required'  => '年份必填',
            'year.numeric'   => '年份必须是数字',
            'month.required' => '月份必选',
            'month.in'       => '月份不在可选范围内'
        ])->validate();
        $year  = $request->input('year') ?? date('Y');
        $month = $request->input('month') ?? date('m');
        if($year == date('Y') && $month == date('m'))
        {
            $start_date = date('Y-m-01',strtotime(date('Y-m-d')));
        }else{
            $start_date = $year.'-'.$month.'-01';
        }
        $end_date   = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        //日活跃
        $logon_num = RecordUserLogon::select(
            'CreateDate',
            \DB::raw('COUNT(*) as total')
        )
            ->andFilterBetweenWhere('CreateDate', $start_date, $end_date)->groupBy('CreateDate')->pluck('total','CreateDate');
        $dates = getDateRange($start_date,$end_date,35);
        //总支付，支付人数
        $recharge_data = PaymentOrder::select(
            \DB::raw("CONVERT(varchar(100), success_time, 23) as success_at"),
            \DB::raw('SUM(amount) as amount'),
            \DB::raw('COUNT(DISTINCT user_id) as people_total')
        )
            ->where('payment_status',PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('success_time', $start_date, $end_date)
            ->groupBy(\DB::raw("CONVERT(varchar(100), success_time, 23)"))
            ->get()->keyBy('success_at');
        //总注册人数
        $before_register_total = AccountsInfo::select(\DB::raw('COUNT(UserID) as total'))
            ->where('IsAndroid', 0)->where('RegisterDate','<',$start_date)->first();
        $register_sum = AccountsInfo::select(
            \DB::raw('CONVERT(varchar(100), RegisterDate, 23) as register_time'),
            \DB::raw('COUNT(UserID) as total')
        )
            ->where('IsAndroid', 0)->andFilterBetweenWhere('RegisterDate', $start_date, $end_date)
            ->groupBy(\DB::raw('CONVERT(varchar(100), RegisterDate, 23)'))
            ->pluck('total', 'register_time')->toArray();
        //总充值人数
        $before_recharge_total = PaymentOrder::select(\DB::raw('COUNT(DISTINCT user_id) as total'))
            ->where('payment_status',PaymentOrder::SUCCESS)->where('created_at','<',$start_date)->first();
        $recharge_sum = PaymentOrder::select(
            \DB::raw('CONVERT(varchar(100), success_time, 23) as success_time'),
            \DB::raw('COUNT(DISTINCT user_id) as total')
        )
            ->where('payment_status',PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('success_time', $start_date, $end_date)
            ->groupBy(\DB::raw('CONVERT(varchar(100), success_time, 23)'))
            ->pluck('total', 'success_time')->toArray();
        $list=[];
        $register_total=[];
        foreach ($dates as $k=>$v){
            //总注册人数
            $register_total[$v]= array_sum($register_sum) + $before_register_total['total'];
            unset($register_sum[$v]);
            //总充值人数
            $recharge_total[$v]= array_sum($recharge_sum) + $before_recharge_total['total'];
            unset($recharge_sum[$v]);
            $list[$k]['date']           = $v;
            $list[$k]['logon_num']      = $logon_num[$v] ?? 0; //日活跃人数
            $list[$k]['recharge_score'] = $recharge_data[$v]['amount'] ?? '0.00';
            $list[$k]['recharge_total'] = $recharge_data[$v]['people_total'] ?? 0;//支付人数
            if($list[$k]['recharge_total']>0 && $list[$k]['logon_num']>0){
                //付费渗透率 = 当日充值人数 / 总注册人数 * 100%
                //更改：付费渗透率 = 支付人数 / 日活跃人数 * 100%
                $list[$k]['register_fee_rate'] = ($list[$k]['recharge_total']/$list[$k]['logon_num']) *100 ?? 0;
            }else{
                $list[$k]['register_fee_rate'] = 0;
            }
            if($list[$k]['recharge_score']>0 && $recharge_total[$v]>0 || $list[$k]['recharge_score']>0 && $register_total[$v]>0){
                $list[$k]['ARPPU'] = $list[$k]['recharge_score']/$recharge_total[$v] ?? 0; //ARPPU = 当日充值 / 总充值人数
                $list[$k]['APRU']  = $list[$k]['recharge_score']/$register_total[$v] ?? 0; //ARPU = 当天充值 / 总注册人数
            }else{
                $list[$k]['ARPPU'] = 0;
                $list[$k]['APRU'] = 0;
            }
        }
        return ResponeSuccess('请求成功', $list);
    }
}
