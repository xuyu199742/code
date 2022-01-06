<?php
//返利报表
namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Models\Treasure\RecordScoreDaily;
use Transformers\RebateReportDetailsTransformer;
use Transformers\RebateReportFormTransformer;
use Validator;

class RebateReportFormController extends Controller
{
    //报表信息
    public function getInfo()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'channel_id' => ['nullable','numeric'] //渠道id
        ], [
            'start_date.date'    => '无效日期',
            'end_date.date'      => '无效日期',
            'channel_id.numeric' => '渠道ID必须是数字'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $channel_id = request('channel_id');
        $channel_list = ChannelInfo::select('channel_id','return_type','balance','created_at')
            ->where('nullity',ChannelInfo::NULLITY_ON)
            ->andFilterWhere('channel_id',$channel_id)
            ->andFilterBetweenWhere('created_at', $start_date, $end_date)
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($channel_list, new RebateReportFormTransformer());
    }
    /**
     * 返利报表玩家详情页统计
     *
     */
    public function rebateReportDetails()
    {
        Validator::make(request()->all(), [
            'channel_id'=> ['required','numeric'],//渠道id
            'game_id'   => ['nullable','numeric'] //玩家id
        ], [
            'game_id.numeric'  => '玩家ID必须是数字'
        ])->validate();
        $channel_id = request('channel_id');
        $game_id = request('game_id');
        $channel_info = ChannelInfo::where('nullity',ChannelInfo::NULLITY_ON)->where('channel_id',$channel_id)->first();
        if (!$channel_info){
            return ResponeFails('渠道不存在或已被禁用！');
        }
        $s_user['UserID']='';
        if($game_id){
            $s_user = AccountsInfo::where('GameID',$game_id)->first();
            if (!$s_user){
                return ResponeFails('查询的用户不存在');
            }
            $exit_sub_user = ChannelUserRelation::where('channel_id',$channel_id)->where('user_id',$s_user['UserID'])->pluck('user_id')->toArray();
            if(!$exit_sub_user){
                return ResponeFails('该用户不属于直推玩家，请重新输入');
            }
        }
        //查询直属玩家信息
        $list = ChannelUserRelation::select('channel_id','user_id')
            ->with('account:UserID,GameID,NickName')
            ->andFilterWhere('user_id',$s_user['UserID'])
            ->where('channel_id',$channel_id)
            ->orderBy('created_at','desc')
            ->paginate(20);
        $today = date('Y-m-d');
        //总充值
        $data['recharge_sum'] = PaymentOrder::select(['user_id',DB::raw('sum(amount) as amount')])
            ->whereIn('user_id',$list->pluck('user_id')->toArray())
            ->where('payment_status',PaymentOrder::SUCCESS)
            ->groupBy('user_id')
            ->pluck('amount','user_id');
        //今日充值
        $data['recharge_today'] = PaymentOrder::select(['user_id',DB::raw('sum(amount) as amount')])
            ->whereIn('user_id',$list->pluck('user_id')->toArray())
            ->where('payment_status',PaymentOrder::SUCCESS)
            ->andFilterBetweenWhere('success_time',$today,$today)
            ->groupBy('user_id')
            ->pluck('amount','user_id');

        $data['withdrawal_sum'] =  WithdrawalOrder::select(['user_id',DB::raw('sum(money) as money')])
            ->whereIn('user_id',$list->pluck('user_id')->toArray())
            ->where('status',WithdrawalOrder::PAY_SUCCESS)
            ->groupBy('user_id')
            ->pluck('money','user_id');

        $data['withdrawal_today'] =  WithdrawalOrder::select(['user_id',DB::raw('sum(money) as money')])
            ->whereIn('user_id',$list->pluck('user_id')->toArray())
            ->where('status',WithdrawalOrder::PAY_SUCCESS)
            ->andFilterBetweenWhere('updated_at',$today,$today)
            ->groupBy('user_id')
            ->pluck('money','user_id');
        //投注，流水，系统输赢，税收
        $data['game_data'] =  RecordScoreDaily::select(
            ['UserID',
                DB::raw('sum(JettonScore) as JettonScore'),
                DB::raw('sum(StreamScore) as StreamScore'),
                DB::raw('sum(RewardScore-JettonScore) as ChangeScore'),
                DB::raw('sum(SystemServiceScore) as SystemServiceScore')
            ])
            ->whereIn('UserID',$list->pluck('user_id')->toArray())
            ->groupBy('UserID')
            ->get();
        $user_info = collect($data['game_data'] ?? [])->keyBy('UserID');
        foreach ($list as $k => $v){
            //总充值
            $list[$k]['recharge_sum'] = $data['recharge_sum'][$v['user_id']] ?? '0.00';
            //今日充值
            $list[$k]['recharge_today'] = $data['recharge_today'][$v['user_id']] ?? '0.00';
            //总支出
            $list[$k]['withdrawal_sum'] = $data['withdrawal_sum'][$v['user_id']] ?? '0.00';
            //今日
            $list[$k]['withdrawal_today'] = $data['withdrawal_today'][$v['user_id']] ?? '0.00';
            //投注
            $list[$k]['jetton_score'] = $user_info[$v['user_id']]['JettonScore'] ?? '0.00';
            //流水
            $list[$k]['stream_score'] = $user_info[$v['user_id']]['StreamScore'] ?? '0.00';
            //系统输赢改为：玩家输赢=中奖-投注
            $list[$k]['system_change_score'] = realCoins($user_info[$v['user_id']]['ChangeScore'] ?? '0.00');
            //税收
            $list[$k]['system_service_score'] = $user_info[$v['user_id']]['SystemServiceScore']?? '0.00';
        }
        return $this->response->paginator($list, new RebateReportDetailsTransformer());
    }
}
