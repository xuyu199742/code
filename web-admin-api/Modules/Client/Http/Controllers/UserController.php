<?php

namespace Modules\Client\Http\Controllers;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Models\Accounts\AccountsFace;
use Models\Accounts\UserLevel;
use Models\AdminPlatform\WithdrawalOrder;
use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\GameCategoryRelation;
use Models\OuterPlatform\OuterPlatformGame;
use Models\OuterPlatform\WashCodeHistory;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordInsure;
use Models\Treasure\UserAuditBetInfo;
use Modules\Client\Http\Requests\AccountDetailRequest;
use Modules\Client\Http\Requests\AccountReportRequest;
use Modules\Client\Http\Requests\JettonScoreRequest;
use Modules\Client\Transformers\AccountDetailTransformer;
use Modules\Client\Transformers\AuditBetScoreResource;
use Modules\Client\Transformers\JettonScoreRecordTransformer;
use Modules\Client\Transformers\RecordInsureResource;
use Transformers\RecordTreasureSerialTransformer;

class UserController extends Controller
{
    //刷新金币
    public function refreshGold($user_id)
    {
        $GameScore = GameScoreInfo::where('UserID', $user_id)->first();
        return ResponeSuccess('刷新成功', $GameScore->Score ?? 0);
    }

    //金币流水
    public function goldWater($user_id)
    {
        $list = RecordTreasureSerial::where('UserID', $user_id)
            ->andFilterWhere('TypeID', request('type_id'))
            ->andFilterBetweenWhere('CollectDate', request('start_date'), request('end_date'))
            ->orderBy('CollectDate', 'desc')
            ->paginate(10);
        return $this->response->paginator($list, new RecordTreasureSerialTransformer());
    }

    public function face($user_id)
    {
        $res = AccountsFace::where('UserID', $user_id)->first();
        if ($res) {
            $http=parse_url($res->FaceUrl);
            if(isset($http['scheme'])){
                return ResponeSuccess('请求成功', ['face' => $res->FaceUrl]);
            }else{
                return ResponeSuccess('请求成功', ['face' => cdn('head/'.$res->FaceUrl)]);
            }

        }
        return ResponeFails('用户头像不存在');
    }

    //转账记录
    public function transferLog($user_id)
    {
        $list = RecordInsure::with(['transfer:UserID,GameID','receiver:UserID,GameID'])->where('TradeType',RecordInsure::TRANSFER)->where('SourceUserID',$user_id)->paginate(30);
        return ResponeSuccess('请求成功',RecordInsureResource::collection($list));
    }

    //收款记录
    public function receiverLog($user_id)
    {
        $list = RecordInsure::with(['transfer:UserID,GameID','receiver:UserID,GameID'])->where('TradeType',RecordInsure::TRANSFER)->where('TargetUserID',$user_id)->paginate(30);
        return ResponeSuccess('请求成功',RecordInsureResource::collection($list));
    }

    //稽核打码量
    public function auditBetInfo($user_id)
    {
        $AuditBet = UserAuditBetInfo::where('UserID',$user_id)->first();
        return ResponeSuccess('刷新成功', $AuditBet->AuditBetScore ?? 0);
    }

    //资金明细
    public function auditBetList($user_id)
    {
        $list = RecordTreasureSerial::where('UserID',$user_id)->orderBy('CollectDate', 'desc')->paginate(30);
        return ResponeSuccess('请求成功',AuditBetScoreResource::collection($list));
    }

    // 投注记录
    public function jettonScoreRecord(JettonScoreRequest $request, $user_id)
    {
        $this->checkUser($user_id);

        $recordGameSub = $this->recordGameBuilder($user_id, $request);
        $list = RecordGameScore::query()->from(RecordGameScore::tableName().' as s')
            ->selectRaw('OrderTime, JettonScore, ChangeScore, OrderNo, g.name as game_name')
            ->joinSub($recordGameSub, 'recordGameSub', function ($join) {
                $join->on('s.id', '=', 'recordGameSub.id');
            })
            ->leftJoin(OuterPlatformGame::tableName().' as g',function ($query) {
                $query->on('g.kind_id','=','s.KindID')
                    ->whereRaw('g.platform_id = s.PlatformID');
            })
            ->orderBy('s.OrderTime', 'desc')
            ->paginate(5);

        return $this->response->paginator($list, new JettonScoreRecordTransformer());
    }

    // 账户明细
    public function accountDetail(AccountDetailRequest $request, $user_id)
    {
        $this->checkUser($user_id);
        $basic_query = RecordTreasureSerial::query()->where('UserID', $user_id);
        // 充值总额
        $recharge_total = realCoins((clone $basic_query)->whereIn('TypeID', RecordTreasureSerial::RECHARGE_TOTAL)->get()->sum('ChangeScore'));
        $withdrawal_total = realCoins(WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS)->get()->sum('real_gold_coins'));
        // $withdrawal_total = abs(realCoins((clone $basic_query)->whereIn('TypeID', [11])->get()->sum('ChangeScore')));
        // 礼金总额
        $active_total = realCoins((clone $basic_query)->whereIn('TypeID', array_keys(RecordTreasureSerial::getTypes(2)))->get()->sum('ChangeScore'));
        // 账户总额
        $total = GameScoreInfo::query()->where('UserID', $user_id)->get();
        $account_total = realCoins($total->sum('Score') + $total->sum('InsureScore'));
        $type_id = $request->type;
        $list = (clone $basic_query)->orderBy('CollectDate', 'desc')->orderBy('TypeID', 'desc')
            ->whereIn('TypeID', RecordTreasureSerial::ClientType(true,4))
            ->where(function ($query) use ($type_id) {
                if ($type_id == RecordTreasureSerial::SYSTEM_GIVE_UP) {
                    $query->where('TypeID', 0)->where('ChangeScore', '>=', 0);  //后台赠送-上分
                } elseif ($type_id == RecordTreasureSerial::SYSTEM_GIVE_DOWN) {
                    $query->where('TypeID', 0)->where('ChangeScore', '<', 0);  //后台赠送-下分
                } else {
                    $query->andFilterWhere('TypeID', $type_id);
                }
            })
            ->AndFilterBetweenWhere('CollectDate', $request->start_time, $request->end_time)
            ->paginate(5);

        return $this->response->paginator($list, new AccountDetailTransformer())
            ->addMeta('RechargeTotal', $recharge_total)
            ->addMeta('WithdrawalTotal', $withdrawal_total)
            ->addMeta('ActiveTotal', $active_total)
            ->addMeta('AccountTotal', $account_total);
    }

    //个人报表
    public function accountReport(AccountReportRequest $request, $user_id)
    {
        $this->checkUser($user_id);
        //游戏记录历史
        $recordGameSub = $this->recordGameBuilder($user_id, $request);
        $gameRecord = RecordGameScore::query()->from(RecordGameScore::tableName().' as s')
            ->selectRaw('JettonScore, ChangeScore')
            ->joinSub($recordGameSub, 'recordGameSub', function ($join) {
                $join->on('s.id', '=', 'recordGameSub.id');
            })
            ->leftJoin(OuterPlatformGame::tableName().' as g',function ($query) {
                $query->on('g.kind_id','=','s.KindID')
                    ->whereRaw('g.platform_id = s.PlatformID');
            })
            ->get();

        $category_id = $request->category_id;
        //洗码历史
        $washCodeHistory = WashCodeHistory::query()->with(['records' => function ($query) use($category_id) {
            $query->where('category_id', $category_id);
        }])
            ->AndFilterWhere('user_id', $user_id)
            ->AndFilterBetweenWhere('created_at', $request->start_time, $request->end_time)
            ->get()
            ->transform(function ($item) {
                $item->wash_code = $item->records->sum('wash_code');
                return $item;
            });

        $data = [
            'JettonScore' => realCoins($gameRecord->sum('JettonScore')), // 投注
            'ChangeScore' => realCoins($gameRecord->sum('ChangeScore')), // 输赢
            'ValidJettonScore' => realCoins($gameRecord->sum('JettonScore') + $gameRecord->sum('ChangeScore')), // 中奖
            'WashCodeTotal' => realCoins( $washCodeHistory->sum('wash_code')) // 洗码总额
        ];

        return ResponeSuccess('请求成功', $data);
    }

    // 个人中心过滤数据
    public function accountFilterData()
    {
        $category = GameCategory::query()->where('tag', '<>', GameCategory::HOT_TAG)->pluck('name', 'id');
        $platform = [];
        $platforms = GameCategoryRelation::query()
            ->with('platform:id,name')
            ->get()
            ->groupBy('category_id');

        foreach ($platforms as $key =>  $items) {
            $platform[$key] = $items->pluck('platform.name', 'platform.id');
        }
        //$type = RecordTreasureSerial::TYPEID;
        return ResponeSuccess('请求成功', ['category' => $category, 'platform' => $platform, 'type' => RecordTreasureSerial::ClientType(false,4)]);
    }

    public function recordGameBuilder($user_id, $request):Builder
    {
        // 排除热门游戏分类
        $recordGameSub = RecordGameScore::query()->from(RecordGameScore::tableName().' as s')
            ->where('s.UserID', $user_id)
            ->selectRaw('s.id, s.KindID, s.PlatformID')
            ->leftJoin(GameCategoryRelation::tableName().' as r', function ($query) {
                $query->on('r.platform_id','=','s.PlatformID');
            })
            ->AndFilterWhere('r.category_id', $request->category_id)
            ->AndFilterWhere('s.PlatformID', $request->platform_id)
            ->AndFilterBetweenWhere('s.UpdateTime', $request->start_time, $request->end_time)
            ->groupBy('s.id', 's.KindID', 's.PlatformID');

        return $recordGameSub;
    }

    // 用户层级列表
    public function userLevel()
    {
        $list = UserLevel::query()->get();
        return ResponeSuccess('获取成功', [$list]);
    }
}
