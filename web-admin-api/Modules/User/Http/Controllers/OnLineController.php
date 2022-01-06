<?php
/*用户在线*/

namespace Modules\User\Http\Controllers;

use App\Http\Requests\SelectGameIdRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\GameScoreLocker;
use Models\Treasure\GameChatUserInfo;
use Transformers\GameChatUserTransformer;
use Transformers\GameScoreLockerTransformer;
use Validator;

class OnLineController extends BaseController
{
    /**
     * 当前在线人数
     *
     * @return mixed
     */
    public function count()
    {
        //初始化值
        $data['total']   = 0;//总在线人数
        $data['android'] = 0;//android人数
        $data['ios']     = 0;//ios人数
        $data['h5']      = 0;//h5
        try {
            //分组统计
            $ios_android = DB::table('WHQJTreasureDB.dbo.GameChatUserInfo as a')
                ->select('b.ClientType', DB::raw("COUNT(*) AS num"))
                ->leftJoin('WHQJAccountsDB.dbo.AccountsInfo AS b', 'a.UserID', '=', 'b.UserID')
                ->whereDate('CollectDate', date('Y-m-d', time()))
                ->groupBy('b.ClientType')
                ->get();
            foreach ($ios_android as $k => $v) {
                if ($v->ClientType == AccountsInfo::CLIENT_TYPE_ANDROID) {
                    $data['android'] = $v->num;//android人数
                } elseif ($v->ClientType == AccountsInfo::CLIENT_TYPE_IOS) {
                    $data['ios'] = $v->num;//ios人数
                } elseif ($v->ClientType == AccountsInfo::CLIENT_TYPE_PC) {
                    $data['h5'] = $v->num;//h5人数
                }
            }
            //统计总数
            $data['total'] = $data['android'] + $data['ios'] + $data['h5'];
        } catch (\ErrorException $e) {
            return ResponeSuccess('请求成功', $data);
        }
        return ResponeSuccess('请求成功', $data);
    }

    /**
     * 在线玩家
     *
     */
    public function getList(SelectGameIdRequest $request)
    {
        Validator::make($request->all(), [
            'KindID'   => ['nullable', 'numeric'],
            'ServerID' => ['nullable', 'numeric'],
        ])->validate();
        $map['ServerID'] = request('server_id');
        $list            = $this->gameIdSearchUserId(request('game_id'), new GameChatUserInfo())
            ->leftJoin(DB::raw('(select KindID,ServerID,EnterID,EnterIP,EnterMachine,UserID as user_id from WHQJTreasureDB.dbo.GameScoreLocker) as b'), 'WHQJTreasureDB.dbo.GameChatUserInfo.UserID', '=', 'b.user_id')
            ->whereDate('CollectDate', date('Y-m-d', time()))
            ->with('channel')
            ->multiWhere($map)->where(function ($query){
                if(request('kind_id')) {
                    if (request('kind_id') == -1) {
                        $query->where('KindID', null);
                    } else {
                        $query->where('KindID', request('kind_id'));
                    }
                }
            })->orderBy('CollectDate', 'desc')->paginate(10);
        foreach ($list as $k=>$v){
            //总充值
            $list[$k]['recharge_sum'] = PaymentOrder::where('user_id',$v['UserID'])->where('payment_status', PaymentOrder::SUCCESS)->sum('amount');

            $list[$k]['withdrawal_sum'] = WithdrawalOrder::where('user_id',$v['UserID'])->where('status',WithdrawalOrder::PAY_SUCCESS)->sum('money');
            //携带金币
            $score= GameScoreInfo::select('Score')->where('UserID',$v['UserID'])->first();
            $list[$k]['score']=realCoins($score['Score']);
        }
        return $this->response->paginator($list, new GameChatUserTransformer());
    }

    /**
     * 清除卡线
     *
     * @param array $user_id 用户id
     *
     */
    public function clearForqkftod()
    {
        Validator::make(request()->all(), [
            'user_id' => ['required', 'array'],
        ])->validate();
        $user_ids = request('user_id');
        if (is_array($user_ids) && !empty($user_ids)) {
            $res = GameScoreLocker::whereIn('UserID', $user_ids)->delete();
            if ($res) {
                return ResponeSuccess('清除成功');
            }
            return ResponeFails('清除失败');
        }
        return ResponeFails('请勾选要清除的');
    }
}
