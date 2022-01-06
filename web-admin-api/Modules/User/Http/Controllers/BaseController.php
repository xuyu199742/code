<?php
/*用户*/
namespace Modules\User\Http\Controllers;
use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentInfo;
use Models\Agent\AgentRelation;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;

class BaseController extends Controller
{
    /**
     * 获取用户账户余额
     *
     * @param   int     $user_id    用户id
     */
    protected function getUserScore($user_id)
    {
        $GameScore = GameScoreInfo::where('UserID', $user_id)->first();
        return $GameScore->Score ?? 0;
    }

    /**
     * 获取用户代理推广余额
     *
     * @param   int     $user_id    用户id
     */
    protected function getUserAgentScore($user_id)
    {
        $GameScore = AgentInfo::where('user_id', $user_id)->first();
        return $GameScore->balance ?? 0;
    }

    /**
     * 获取用户充值
     *
     * @param   int     $user_id    用户id
     */
    protected function getPaySum($user_id,$is_today = false,$request_data = null)
    {
        $obj = PaymentOrder::where('user_id', $user_id)->where('payment_status', PaymentOrder::SUCCESS);
        if($request_data['date_type']==2) //登入日期
        {
            $obj->andFilterBetweenWhere('success_time',request('start_date'),request('end_date'));
        }
        if ($is_today === true){
            $obj->whereDate('created_at', date("Y-m-d"));
        }
        return $obj->sum('coins');
    }

    /**
     * 获取用户充值次数
     *
     * @param   int     $user_id    用户id
     */
    protected function getPayTimes($user_id,$is_today = false)
    {
        $obj = PaymentOrder::where('user_id', $user_id)->where('payment_status', PaymentOrder::SUCCESS);
        if ($is_today === true){
            $obj->whereDate('created_at', date("Y-m-d"));
        }
        return $obj->count();
    }

    /**
     * 获取用户
     *
     * @param   int     $user_id    用户id
     */
    protected function getWithdrawSum($user_id,$is_today = false,$request_data = null)
    {
        $obj = WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS);
        if($request_data['date_type']==2) //登入日期
        {
            $obj->andFilterBetweenWhere('complete_time',request('start_date'),request('end_date'));
        }
        if ($is_today === true){
            $obj->whereDate('created_at', date("Y-m-d"));
        }
        return $obj->sum('real_gold_coins');
    }

    /**
     * 获取用户次数
     *
     * @param   int     $user_id    用户id
     */
    protected function getWithdrawTimes($user_id,$is_today = false)
    {
        $obj = WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS);
        if ($is_today === true){
            $obj->whereDate('created_at', date("Y-m-d"));
        }
        return $obj->count();
    }

    /**
     * 获取用户输赢
     *
     * @param   int     $user_id    用户id
     */
    protected function getWinloseSum($user_id,$is_today = false)
    {
        $obj = RecordScoreDaily::where('UserID', $user_id);
        if ($is_today === true){
            $obj->whereDate('UpdateDate', date("Y-m-d"));
        }
        return $obj->sum(\DB::raw('ChangeScore'));//改为：玩家输赢=中奖-投注
    }

    /**
     * 统计用户流水
     *
     * @param   int     $user_id    用户id
     */
    protected function getUserCount($user_id,$is_today = false,$request_data = null)
    {
        $obj =  RecordScoreDaily::select(
                \DB::raw('sum(ChangeScore) as WinScore'),//输赢（损益）
                \DB::raw('sum(JettonScore) as JettonScore'),//有效投注
                \DB::raw('sum(StreamScore) as StreamScore')//流水
            )->where('UserID', $user_id);
        if($request_data['date_type']==2) //登入日期
        {
            $obj->andFilterBetweenWhere('UpdateDate',request('start_date'),request('end_date'));
        }
        if ($is_today === true){
            $obj->whereDate('UpdateDate', date("Y-m-d"));
        }
        return $obj->first();
    }
    /**
     * 统计用户下注量
     *
     * @param   int     $user_id    用户id
     */
    protected function getUserJettonScore($user_id,$is_today = false,$type = false)
    {
        $obj = RecordScoreDaily::where('UserID', $user_id);
        if ($is_today === true){
            $obj->whereDate('UpdateDate', date("Y-m-d"));
        }
        if($type === true) {
            return $obj->sum('JettonScore');
        }else{
            return $obj->sum('ValidJettonScore');
        }
    }

    //获取父级的game_id
    protected function getParentGameId($user_id)
    {
        try{
            $agent = AgentRelation::where('user_id',$user_id)->first();
            return $this->getGameId($agent->parent_user_id);
        }catch (\Exception $exception){
            return 0;
        }
    }

}
