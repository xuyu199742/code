<?php
namespace Modules\Statistics\Http\Service;


use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\OuterPlatform\OuterPlatformGame;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;

class StaticsService
{
    public function staticsOrder($user_id, $time)
    {
        $statics = [];
        //当日充值
        try {
            $newRecharge = PaymentOrder::where('payment_status', PaymentOrder::SUCCESS)
                ->where('user_id', $user_id)
                ->whereDate('created_at', date('Y-m-d', strtotime($time)))
                ->sum('amount');
        } catch (\ErrorException $e) {
            $newRecharge = 0;
        }
        $statics['recharge'] = $newRecharge;

        //总充值
        try {
            $new_total_recharge = PaymentOrder::where('payment_status', PaymentOrder::SUCCESS)
                ->where('user_id', $user_id)
                ->sum('amount');
        } catch (\ErrorException $e) {
            $new_total_recharge = 0;
        }
        $statics['total_recharge'] = $new_total_recharge;
        //当日输赢改为：当日玩家输赢=中奖-投注
        $statics['win_lose'] = RecordScoreDaily::where('UserID', $user_id)
            ->whereDate('UpdateDate', date('Y-m-d', strtotime($time)))
            ->sum(\DB::raw('RewardScore-JettonScore'));
        if ($statics['win_lose'] != 0) {
            $statics['win_lose'] = realCoins($statics['win_lose']);
        }
        //总输赢改为：总玩家输赢=中奖-投注
        /*$statics['all_win_lose'] = RecordScoreDaily::where('UserID', $user_id)
            ->sum(\DB::raw('RewardScore-JettonScore'));
        if ($statics['all_win_lose'] != 0) {
            $statics['all_win_lose'] = realCoins($statics['all_win_lose']);
        }*/
        //总输赢
        $statics['all_win_lose'] = realCoins(RecordGameScore::sumWinLose($user_id) ?? 0);
            //当日
        $statics['withdraw'] = WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS)
            ->whereDate('created_at', date('Y-m-d', strtotime($time)))
            ->sum('money');
        if ($statics['withdraw'] != 0) {
            $statics['withdraw'] = $statics['withdraw'];
        }
        //总
        $statics['total_withdraw'] = WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS)
            ->sum('money');
        if ($statics['total_withdraw'] != 0) {
            $statics['total_withdraw'] = $statics['total_withdraw'];
        }

        //当日次数
        $statics['withdraw_times'] = WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS)
            ->whereDate('created_at', date('Y-m-d', strtotime($time)))
            ->count();
        //总次数
        $statics['withdraw_all_times'] = WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS)
            ->count();
        //累计所玩游戏
       /* $statics['game_kinds'] = RecordGameScore::select(['KindID', \DB::raw('SUM(ChangeScore) as ChangeScore')])
            ->with('kind:KindID,KindName')
            ->where('UserID', $user_id)
            ->whereDate('UpdateTime', date('Y-m-d', strtotime($time)))
            ->groupBy('KindID')
            ->get();*/
        $statics['game_kinds'] = RecordGameScore::from(RecordGameScore::tableName() . ' AS a')
            ->leftJoin(OuterPlatformGame::tableName().' as b',function ($join){
                $join->on('a.KindID','=','b.kind_id')->on('a.PlatformID','=','b.platform_id');
            })->select(['a.KindID','a.PlatformID','b.name',\DB::raw('SUM(a.ChangeScore) as ChangeScore')])
            ->where('a.UserID', $user_id)
            ->whereDate('a.UpdateTime', date('Y-m-d', strtotime($time)))
            ->groupBy('a.KindID','a.PlatformID','b.name')
            ->get();
        $win_lose = 0;
        foreach ($statics['game_kinds'] as $key => $value) {
            $statics['game_kinds'][$key]['ChangeScore'] = realCoins($statics['game_kinds'][$key]['ChangeScore'] ?? 0);
            $statics['game_kinds'][$key]['kind_name'] = $statics['game_kinds'][$key]['name'] ?? '';
            $win_lose += $statics['game_kinds'][$key]['ChangeScore'] ?? 0;
        }
        $statics['win_lose'] = bcadd(($win_lose ?? 0), 0, 2);
        return $statics;
    }
}
