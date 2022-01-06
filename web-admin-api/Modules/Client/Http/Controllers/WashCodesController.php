<?php

namespace Modules\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Models\Accounts\AccountsInfo;
use Models\Accounts\MembersInfo;
use Models\Accounts\SystemStatusInfo;
use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\GameCategoryRelation;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\WashCodeHistory;
use Models\OuterPlatform\WashCodeRecord;
use Models\OuterPlatform\WashCodeSetting;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\UserAuditBetInfo;
use Modules\Client\Http\Requests\GetWashCodeHistoryRequest;
use Modules\Client\Transformers\GetWashCodeHistoryTransformer;
use Modules\Client\Transformers\GetWashCodeRecordTransformer;

class WashCodesController extends Controller
{
    /**
     * 获取游戏分类
     */
    public function getWashCodeGameCategory()
    {
        $list = GameCategory::where('tag', '<>', GameCategory::HOT_TAG)->orderBy('sort')->get();
        return ResponeSuccess('请求成功', $list);
    }

    /*
    * 由GameID得到UserID
    * */
    public function getUserID($game_id)
    {
        return AccountsInfo::where('GameID', $game_id)->value('UserID');

    }

    /**
     * 获取用户洗码列表中的以下数据
     * 有效投注：上次结算之后的有效投注总额
     * 上次结算的时间
     * 本次可以洗码的
     * game_id 游戏id必传
     */
    public function getWashCodeData($game_id)
    {
        $list = $this->getBeforeWashCodeData($game_id)->get();
        $transform_data = $this->getWashCodeForVip($list, $game_id);
        $last_date = WashCodeRecord::where('game_id', $game_id)->orderBy('created_at', 'desc')->first()->created_at ?? null;
        $result = [
            'sum_wash_score' =>  $transform_data->sum('wash_score'),
            'sum_bet' => $transform_data->sum('sum_jetton_score'),
            'last_date' => $last_date ? Carbon::parse($last_date)->format('Y-m-d') : '--',
        ];

        return ResponeSuccess('请求成功', $result);
    }

    /**
     * 洗码列表中的按分类未洗码的记录
     * category_id    游戏分类id必传
     * game_id        游戏id必传
     */
    public function getWashCodeList($category_id, $game_id)
    {
        $list = $this->getBeforeWashCodeData($game_id, $category_id)->orderBy('PlatformID', 'asc')->paginate(10)->toArray();

        $list['data'] = $this->getWashCodeForVip(collect($list['data']), $game_id, $category_id);

        return ResponeSuccess('请求成功', $list);
    }

    /**
     * 洗码比例中获取游戏分类中的平台或者游戏
     * category_id  游戏分类id必传
     */
    public function getPlatformOrGame($category_id)
    {
        $games = GameCategoryRelation::from(GameCategoryRelation::tableName() . ' AS a')
            ->select(
                \DB::raw('(select name from ' . OuterPlatform::tableName() . ' where a.platform_id = id) as name'),
                'a.platform_id', 'a.category_id')
            ->where('category_id', $category_id)
            ->groupBy('a.platform_id', 'a.category_id')
            ->get()
            ->map(function ($item) {
                $item->kind_id = 0;
                return $item;
            });

        return ResponeSuccess('获取成功', $games);
    }


    /**
     * 洗码比例
     * category_id  游戏分类id必传
     * game_id      游戏id必传
     */
    public function getWashCodeRetio($category_id, $platform_id, $kind_id, $game_id)
    {
        $list = [];
        $user_vip = AccountsInfo::where('UserID', $this->getUserID($game_id))->where('MemberOrder', '>', 0)->value('MemberOrder');
        $nex_vip = null;
        if ($user_vip < MembersInfo::MaxLevel) {
            $nex_vip = $user_vip + 1;
        }
        $wash_codes = WashCodeSetting::query()->Platform($platform_id, $category_id)
            ->selectRaw('platform_id, category_id, upper_limit, MIN(id) AS id')
            ->with(['vips' => function ($query) use ($user_vip, $nex_vip) {
                $query->where('member_order', $user_vip)
                    ->when($nex_vip, function ($query) use ($nex_vip) {
                        $query->orWhere('member_order', $nex_vip);
                    })
                    ->orderBy('member_order', 'asc');
            }])
            ->groupBy('platform_id', 'category_id', 'upper_limit')
            ->get();
        $list['current_level'] = $this->getWashCodeVipProportion($wash_codes, $user_vip);
        $list['next_level'] = $this->getWashCodeVipProportion($wash_codes, $nex_vip) ?? [];

        return ResponeSuccess('请求成功', $list);
    }

    // 洗码记录
    public function getWashCodeHistory($user_id)
    {
        $this->checkUser($user_id);
        $history = WashCodeHistory::query()->with('records')
            ->AndFilterWhere('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->response->paginator($history, new GetWashCodeHistoryTransformer());
    }

    // 洗码详情
    public function getWashCodeRecord(GetWashCodeHistoryRequest $request, $wash_code_history_id)
    {
        $record = WashCodeRecord::query()->where('wash_code_history_id', $wash_code_history_id)
            ->where('category_id', $request->category_id)
            ->with(['platform:id,name', 'category:id,name'])
            ->paginate(10);

        return $this->response->paginator($record, new GetWashCodeRecordTransformer());
    }

    // 获取玩家未洗码注单数据
    public function getBeforeWashCodeData($game_id, $category_id = null)
    {
        $start_date = $this->getWahCodeStartDate($game_id);
        $builder = RecordGameScore::from(RecordGameScore::tableName() . ' AS a')
            ->selectRaw('a.PlatformID as platform_id, SUM(a.JettonScore) AS sum_jetton_score, p.name as name')
            ->leftJoin(GameCategoryRelation::tableName() . ' as b', function ($query) {
                $query->on('b.platform_id', '=', 'a.PlatformID');
            })
            ->leftJoin(OuterPlatform::tableName() . ' as p', function ($query) {
                $query->on('p.id', '=', 'a.PlatformID');
            })
            ->andFilterWhere('a.UpdateTime', '>', $start_date)
            ->andFilterWhere('b.category_id', $category_id)
            ->where('a.UserID', $this->getUserID($game_id))
            ->groupBy('a.PlatformID', 'p.name')
            ->havingRaw('SUM(a.JettonScore) >= 10000');

        return $builder;
    }

    //获取当前用户vip比例算出洗码
    public function getWashCodeForVip(Collection $list, $game_id, $category_id = null)
    {
        $user_vip = AccountsInfo::where('UserID', $this->getUserID($game_id))->where('MemberOrder', '>', 0)->value('MemberOrder');

        return $list->transform(function ($item) use ($category_id, $user_vip) {
            $basic_query = WashCodeSetting::query()->where('platform_id', $item['platform_id'])
                ->andFilterWhere('category_id', $category_id)
                ->with(['vips' => function ($query) use ($user_vip) {
                    $query->where('member_order', $user_vip);
                }]);
            $wash_code = (clone $basic_query)->where('upper_limit', '>=', $item['sum_jetton_score'])->orderBy('upper_limit')->first();
            if (empty($wash_code)) {
                $wash_code = (clone $basic_query)->orderBy('upper_limit', 'desc')->first();
            }
            $vip = bcadd(($wash_code->vips[0]->vip_proportion ?? 0), 0, 2);
            $item['vip_proportion'] = $vip;
            $item['wash_score'] = realCoins($item['sum_jetton_score'] * $vip * 0.01) ?? 0;
            $item['sum_jetton_score'] = realCoins($item['sum_jetton_score']);
            return $item;
        });
    }

    // 获取洗码计算开始时间
    public function getWahCodeStartDate($game_id)
    {
        //玩家上次结算后的数据,玩家最后一条领取记录时间
        $record = WashCodeRecord::where('game_id', $game_id)->orderBy('created_at', 'desc')->first();
        if ($record) {
            $start_date = $record['created_at'];
        } else {
            $default_date = SystemStatusInfo::where('StatusName', 'WashCodeStartTime')->value('StatusValue');
            $start_date = $default_date ? date('Y-m-d H:i:s', $default_date) : '';
        }
        return $start_date;
    }

    //处理洗码比例数据
    public function getWashCodeVipProportion($wash_codes, $vip_level)
    {
        $list = [];
        foreach ($wash_codes as $item) {
            $vip = $item->vips->firstWhere('member_order', $vip_level);
            if (!$vip) {
                return [];
            }
            $list[] = [
                'upper_limit' => realCoins($item->lower_limit) . '+',
                'member_order' => 'VIP' . $vip->member_order,
                'vip_proportion' => bcadd($vip->vip_proportion, 0, 2), //洗码比例
            ];
        }
        return $list;
    }

    public function getWashCodeScore(){
        /*$user_id = request('user_id');
        if(!$user_id){
            return [];
        }
        $ip = request()->getClientIp();
        try{
            $res = \DB::connection(GameScoreInfo::connectionName())->select('exec GSP_GR_GetWashCode ?,?', [$user_id, $ip]);
        }catch (\Exception $e){
            \Log::warning('获取洗码失败 '.$user_id,[$e->getMessage()]);
            return [];
        }
        $result = (array)$res[0] ?? [];
        if($result){
            if($result['wash_code'] > 0){
                giveInform($user_id,$result['Score'],$result['wash_code']);
            }
        }
        return $result;*/
        $result = [];
        $user_id = request('user_id');
        \Log::info('用户 '.$user_id.'开始洗码,时间:'.microtime());
        $AccountsInfo = AccountsInfo::where('UserID',$user_id)->first();
        $WashCodeSetting = new WashCodeSetting();
        $gameRecord = $WashCodeSetting->getWashCodeData($user_id);
        if ($gameRecord->isEmpty()){
            \Log::info('用户 '.$user_id.'暂无可洗码金额');
            return $result;
        }
        //最后一次洗码记录
        $WashCodeRecord = WashCodeRecord::where('game_id',$AccountsInfo->GameID)->orderBy('id','desc')->first();

        $win_lose    = $WashCodeRecord->aggregate_change_score ?? 0;
        $wash_code   = $WashCodeRecord->aggregate_wash_code ?? 0;//当前洗码总额
        $bet_score   = $WashCodeRecord->jetton_total_score ?? 0;//当前投注总额
        $aggregate_wash_code = $WashCodeRecord->aggregate_wash_code ?? 0;
        $data = [];
        foreach ($gameRecord as $k => $v){
            $ratio = $WashCodeSetting->getCurWashCodeRatio($v->platform_id, $v->sum_jetton_score, $AccountsInfo->MemberOrder);
            $wash_ratio = $ratio->vips[0]['vip_proportion'] ?? 0;
            $v->wash_ratio = $wash_ratio;
            $v->wash_score = intval($v->sum_jetton_score * $wash_ratio / 100);

            $wash_code += $v->wash_score;
            $bet_score += $v->sum_jetton_score;
            $win_lose += $v->win_lose;

            $data[$k]['category_id']         = $v->category_id;
            $data[$k]['platform_id']         = $v->platform_id;
            $data[$k]['aggregate_wash_code'] = $wash_code;
            $data[$k]['jetton_score']        = $v->sum_jetton_score;
            $data[$k]['wash_code']           = $v->wash_score;
            $data[$k]['change_score']        = $v->win_lose;
            $data[$k]['Retio']               = (float)$wash_ratio;
        }
        $db_outer_platform = \DB::connection('outer_platform');
        $db_treasure = \DB::connection('treasure');
        $db_record = \DB::connection('record');
        $db_outer_platform->beginTransaction();
        $db_treasure->beginTransaction();
        $db_record->beginTransaction();
        try {
            //记录历史洗码
            $WashCodeHistory = new WashCodeHistory();
            $WashCodeHistory->user_id = $user_id;
            $WashCodeHistory->created_at = date('Y-m-d H:i:s');
            $res = $WashCodeHistory->save();
            //领取记录
            foreach ($data as $k => $v) {
                $data[$k]['wash_code_history_id']    = $WashCodeHistory->id;
                $data[$k]['game_id']                 = $AccountsInfo->GameID;
                $data[$k]['kind_id']                 = 0;
                $data[$k]['jetton_total_score']      = $bet_score;
                $data[$k]['aggregate_change_score']  = $win_lose;
                $data[$k]['created_at']              = date('Y-m-d H:i:s');
            }
            $res2 = $db_outer_platform->table(WashCodeRecord::tableName())->insert($data);
            //增加金币
            $GameScoreInfo = GameScoreInfo::where('UserID',$user_id)->first();
            $curscore = $wash_code - $aggregate_wash_code;
            $res3 = GameScoreInfo::where('UserID',$user_id)->increment('Score', $curscore);
            //稽核打码
            $AuditBetScoreTake = SystemStatusInfo::where('StatusName','AuditBetScoreTake')->value('StatusValue');
            $UserAuditBetInfo = UserAuditBetInfo::where('UserID',$user_id)->first();
            $wash_bet = intval($curscore * $AuditBetScoreTake / 100);
            if ($UserAuditBetInfo){
                $UserAuditBetInfo->AuditBetScore += $wash_bet;
            }else{
                $UserAuditBetInfo = new UserAuditBetInfo();
                $UserAuditBetInfo->UserID = $user_id;
                $UserAuditBetInfo->AuditBetScore = $wash_bet;
            }
            $res4 = $UserAuditBetInfo->save();
            //流水记录
            $res5 = RecordTreasureSerial::addRecord($user_id, $GameScoreInfo->Score, $GameScoreInfo->InsureScore, $curscore,RecordTreasureSerial::WASH_CODE_SCORE,0,'','',$wash_bet);
            if ($res && $res2 && $res3 && $res4 && $res5){
                $db_outer_platform->commit();
                $db_treasure->commit();
                $db_record->commit();
                \Log::info('用户 '.$user_id.'洗码成功,时间:'.microtime());
                if($curscore > 0){
                    $result['Score'] = $GameScoreInfo->Score + $curscore;
                    $result['wash_code'] = $curscore;
                    giveInform($user_id,$result['Score'],$curscore);
                }
                return $result;
            }else{
                $db_outer_platform->rollBack();
                $db_treasure->rollBack();
                $db_record->rollBack();
                \Log::info('用户 '.$user_id.'洗码失败');
                return $result;
            }

        }catch (\Exception $e){
            \Log::warning('获取洗码失败 '.$user_id,[$e->getMessage()]);
            $db_outer_platform->rollBack();
            $db_treasure->rollBack();
            $db_record->rollBack();
            return $result;
        }

    }
}
