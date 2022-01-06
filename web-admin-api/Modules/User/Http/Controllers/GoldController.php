<?php
/*用户金币*/

namespace Modules\User\Http\Controllers;

use App\Exceptions\NewException;
use App\Http\Requests\SelectGameIdRequest;
use App\Jobs\AllGiveGold;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery\Exception;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\GameControlLog;
use Models\AdminPlatform\TempAddScore;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
use Models\Treasure\GameScoreInfo;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordGrantTreasure;
use Models\Treasure\RecordDrawInfo;
use Models\Treasure\RecordDrawScore;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordInsure;
use Models\Treasure\UserAuditBetInfo;
use Modules\User\Http\Requests\GoldGiveRequest;
use Transformers\RecordDrawScoreTransformer;
use Transformers\RecordGameScoreTransformer;
use Transformers\RecordInsureTransformer;
use Transformers\RecordTreasureSerialTransformer;
use Validator;

class GoldController extends BaseController
{
    /**
     * 金币记录---用户数据统计（游戏内）
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getList(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
        ])->validate();
        /*$list = RecordDrawScore::table()->whereHas('account', function ($query) use ($request){
            $query->from(AccountsInfo::tableName())->andFilterWhere('GameID',$request->input('game_id'));
        })->with(['account'=>function($query) use ($request){
            $query->select('UserID','GameID')->andFilterWhere('GameID',$request->input('game_id'));
        }])->whereHas('darwInfo', function($query) use ($request){
            $query->select('*');
        })->with(['darwInfo'=>function($query) use($request){
            $query->select('*');
        }])->andFilterBetweenWhere('InsertTime',request('start_date'),request('end_date'))
            ->orderBy('InsertTime', 'desc')
            ->paginate(config('page.list_rows'));*/
        $list = RecordDrawScore::from(RecordDrawScore::tableName().' AS a')
            ->select('a.*','b.KindID','b.ServerID','b.Waste','b.BankerCards','c.KindName','d.ServerName','e.GameID',
                \DB::raw('a.RewardScore-a.JettonScore AS winlose')
            )
            ->leftJoin(RecordDrawInfo::tableName().' AS b','a.DrawID','=','b.DrawID')
            ->leftJoin(GameKindItem::tableName().' AS c','b.KindID','=','c.KindID')
            ->leftJoin(GameRoomInfo::tableName().' AS d','b.ServerID','=','d.ServerID')
            ->leftJoin(AccountsInfo::tableName().' AS e','a.UserID','=','e.UserID')
            ->andFilterWhere('e.GameID', request('game_id'))
            ->andFilterBetweenWhere('a.InsertTime',request('start_date'),request('end_date'))
            ->orderBy('a.InsertTime','desc')
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RecordDrawScoreTransformer());
    }

    /**
     * 金币赠送
     *
     */
    public function give(GoldGiveRequest $request)
    {
        try {
            $db_treasure = DB::connection('treasure');
            $db_record   = DB::connection('record');
            $db_treasure->beginTransaction();
            $db_record->beginTransaction();
            \DB::beginTransaction();
            $title='';
            $action='';
            $game_id   = request('game_id');
            $add_gold  = request('add_gold') * getGoldBase();//金币数
            $multiple  = request('multiple'); //稽核打码量倍数
            $reason    = request('reason') ?? 0;//金币数备注信息（赠送原因）
            $audit_bet = floor($multiple * getGoldBase());
            //用户检测
            $user_id = (new AccountsInfo)->getUserId($game_id);
            if (!$user_id) {
                throw new NewException('用户不存在');
            }
            if($add_gold > 0){
                $action = GameControlLog::USER_SCORE_UP; //上分的动作类型
                $title = '上分';
            }else{
                $action = GameControlLog::USER_SCORE_DOWN; //下分的动作类型
                $title = '下分';
                //外接平台在线不允许操作
                $GameScoreInfo = GameScoreInfo::where('UserID',$user_id)->first();
                if ($GameScoreInfo->CurPlatformID != 0){
                    throw new NewException('账号处于游戏状态，不能下分');
                }
            }
            //查询用户金币数
            $GameScore = GameScoreInfo::where('UserID', $user_id)->first();
            //金币扣除后不能为负数
            if (($GameScore->Score + $add_gold) < 0) {
                throw new NewException('扣除后不能为负数');
            }
            if ($add_gold == 0){
                throw new NewException('请输入大于0的数字');
            }
            //增加用户金币数
            $res = GameScoreInfo::where('UserID', $user_id)->increment('Score', $add_gold);
            //生成赠送记录
            $res2 = RecordTreasureSerial::addRecord($user_id, $GameScore->Score, $GameScore->InsureScore, $add_gold, RecordTreasureSerial::SYSTEM_GIVE_TYPE, $this->user()->id ?? 0,$reason,0,$audit_bet);
            $res3 = RecordGrantTreasure::add($user_id, $GameScore->Score, $add_gold, $reason, $this->user()->id ?? 0);
            //查询用户稽核打码
            $model = UserAuditBetInfo::where('UserID', $user_id)->first();
            if($model){
                $model->AuditBetScore = $model->AuditBetScore + $audit_bet;
            }else{
                $model = new UserAuditBetInfo();
                $model->AuditBetScore = $audit_bet;
            }
            $model -> UserID = $user_id;
            if ($res && $res2 && $res3 && $model->save()) {
                $db_treasure->commit();
                $db_record->commit();
                \DB::commit();
                //赠送通知
                giveInform($user_id, $GameScore->Score + $add_gold, $add_gold, 1);
                GameControlLog::addOne('玩家'.$title, '给玩家：' .$game_id.' '.$title.','.abs(request('add_gold')), $action,GameControlLog::SUCCESS);
                return ResponeSuccess('操作成功');
            } else {
                $db_treasure->rollback();
                $db_record->rollback();
                \DB::rollBack();
                GameControlLog::addOne('玩家'.$title, '给玩家：' .$game_id.' '.$title.','.abs(request('add_gold')), $action,GameControlLog::FAILS);
                return ResponeFails('操作失败');
            }
        } catch (NewException $e) {
            $db_treasure->rollback();
            $db_record->rollback();
            \DB::rollBack();
            GameControlLog::addOne('玩家'.$title, '给玩家：' .$game_id.' '.$title.','.abs(request('add_gold')), $action,GameControlLog::FAILS);
            return ResponeFails('操作失败：'.$e->getMessage());
        } catch (\Exception $e) {
            $db_treasure->rollback();
            $db_record->rollback();
            \DB::rollBack();
            GameControlLog::addOne('玩家'.$title, '给玩家：' .$game_id.' '.$title.','.abs(request('add_gold')), $action,GameControlLog::FAILS);
            return ResponeFails('操作失败');
        }
    }

    /**
     * 金币流水(游戏外)
     *
     * @return \Dingo\Api\Http\Response
     */
    public function generalWater(SelectGameIdRequest $request)
    {
        $type_ids = request('type_id') ?? [];
        $data = $this->gameIdSearchUserId(request('game_id'), new RecordTreasureSerial())
            ->andFilterWhere('UserID', request('user_id'));
        $data->where(function ($query) use ($type_ids) {
            $query->where(function($query) use ($type_ids){
                if (in_array(RecordTreasureSerial::SYSTEM_GIVE_UP, $type_ids) && !in_array(RecordTreasureSerial::SYSTEM_GIVE_DOWN, $type_ids)) //后台赠送-上分
                {
                    $query->where('TypeID', 0)->where('ChangeScore', '>=', 0);
                } elseif(in_array(RecordTreasureSerial::SYSTEM_GIVE_DOWN, $type_ids) && !in_array(RecordTreasureSerial::SYSTEM_GIVE_UP, $type_ids)) //后台赠送-下分
                {
                    $query->where('TypeID', 0)->where('ChangeScore', '<', 0);
                }elseif(in_array(RecordTreasureSerial::SYSTEM_GIVE_UP, $type_ids) && in_array(RecordTreasureSerial::SYSTEM_GIVE_DOWN, $type_ids)) //后台赠送-上下分
                {
                    $query->where('TypeID', 0);
                }
            }) ->when($type_ids, function ($query) use ($type_ids) {
                $query->orWhereIn('TypeID', $type_ids);
            });
        });
        $list = $data->andFilterBetweenWhere('CollectDate', request('start_date'), request('end_date'))
            ->orderBy('CollectDate', 'desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RecordTreasureSerialTransformer())->addMeta('type', RecordTreasureSerial::ClientType(false,1));
    }
    /**
     * 金币流水(游戏输赢记录)
     * 已废弃
     * @return \Dingo\Api\Http\Response
     */
    public function gameWater(SelectGameIdRequest $request)
    {
        $list = $this->gameIdSearchUserId(request('game_id'), new RecordGameScore())
            ->andFilterBetweenWhere('UpdateTime', request('start_date'), request('end_date'))
            ->orderBy('UpdateTime', 'desc')
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RecordGameScoreTransformer());
    }

    //转账记录
    public function transferLog()
    {
        Validator::make(request()->all(), [
            'SourceUserID'    => ['nullable', 'numeric'],
            'TargetUserID'    => ['nullable', 'numeric'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
        ], [
            'SourceUserID.numeric' => '转款人Id必须数字',
            'TargetUserID.numeric' => '收款人Id必须数字',
            'start_date.date'      => '无效日期',
            'end_date.date'        => '无效日期',
        ])->validate();
        $list = $this->gameIdSearchUserId(request('TargetUserID'), $this->gameIdSearchUserId(request('SourceUserID'), new RecordInsure(),'SourceUserID'),'TargetUserID')
            ->where('TradeType',RecordInsure::TRANSFER)
            ->andFilterBetweenWhere('CollectDate',request('start_date'),request('end_date'))
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RecordInsureTransformer());
    }

    //批量上分
    public function batchAddScore()
    {
        try {
            $i = 2;
            $arr = [];
            $err = ['code' => 1];
            //验证并重组数据
            $info = [];
            $temp_arr = [];
            $data = request('import');
            if (empty($data)) {
                return ResponeFails('上传数据失败');
            }
            if (count($data) != count(array_unique(array_column($data, 'game_id')))) {
                return ResponeFails('用户ID不能重复');
            }
            foreach ($data as $k => $v) {
                //变量不存在判断
                if (!isset($v['game_id']) || !isset($v['add_gold']) || !isset($v['multiple']) || !isset($v['reason'])) {
                    return ResponeFails('上传数据格式有误');
                }
                //验证用户
                if (!is_numeric($v['game_id']) || $v['game_id'] <= 0 || floor($v['game_id']) != $v['game_id']) {
                    return ResponeFails('第' . $i . '行玩家ID' . $v['game_id'] . '，玩家输入有误', $err);
                }
                //验证用户是否存在
                $AccountsInfo = AccountsInfo::from('AccountsInfo as a')
                    ->select('a.UserID', 'b.Score', 'b.InsureScore')
                    ->leftJoin(GameScoreInfo::tableName() . ' as b', 'a.UserID', '=', 'b.UserID')
                    ->where('a.GameID', $v['game_id'])
                    ->where('a.IsAndroid', 0)
                    ->first();
                if (!$AccountsInfo) {
                    return ResponeFails('第' . $i . '行玩家ID' . $v['game_id'] . '，玩家不存在', $err);
                }
                $user_id = $AccountsInfo->UserID;
                //上分金额
                if (!is_numeric($v['add_gold']) || $v['add_gold'] <= 0) {
                    return ResponeFails('第' . $i . '行玩家ID' . $v['game_id'] . '，上分金额必须大于0', $err);
                }
                //稽核打码
                if (!is_numeric($v['multiple']) || $v['multiple'] < 0) {
                    return ResponeFails('第' . $i . '行玩家ID' . $v['game_id'] . '，稽核打码必须是数字，不能小于0', $err);
                }
                $add_gold = floor($v['add_gold'] * getGoldBase());  //存入数据库必须是整数，否则报错
                $audit_bet = floor($v['multiple'] * getGoldBase());
                //流水数据
                $info[] = [
                    'UserID'           => $user_id,
                    'MasterID'         => $this->user()->id ?? 0,
                    'TypeID'           => RecordTreasureSerial::SYSTEM_GIVE_TYPE,//记录类型
                    'CurScore'         => $AccountsInfo->Score,//当前金币数
                    'CurInsureScore'   => $AccountsInfo->InsureScore,//保险箱存款金币
                    'ChangeScore'      => $add_gold,//变化值（上分金额）
                    'CurAuditBetScore' => $audit_bet,//稽核打码
                    'Reason'           => $v['reason'],
                    'SerialNumber'     => 'no' . msectime() . rand(1000, 9999),
                    'ClientIP'         => getIp() ?? '0.0.0.0',
                    'CollectDate'      => Carbon::now(),
                    'OrderID'          => 0
                ];
                //临时表数据
                $temp_arr[] = [
                    'user_id'   => $user_id,
                    'add_gold'  => $add_gold,//变化值（上分金额）
                    'audit_bet' => $audit_bet,//稽核打码
                ];
                //通知数据
                $arr[] = [
                    'user_id'  => $user_id,
                    'curscore' => $AccountsInfo->Score + $add_gold,
                    'add_gold' => $add_gold
                ];
                $i++;
            }
        }catch (Exception $e){
            GameControlLog::addOne('批量上分', '给玩家批量上分', GameControlLog::USER_SCORE_UP,GameControlLog::FAILS);
            return ResponeFails('数据验证失败');
        }
        try {
            DB::connection('treasure')->beginTransaction();
            DB::connection('record')->beginTransaction();
            DB::beginTransaction();
            //创建临时表
            if (!\Schema::hasTable('temp_add_score')) {
                \Schema::create(TempAddScore::tableName(), function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('user_id')->unique('user_id');
                    $table->bigInteger('add_gold');
                    $table->bigInteger('audit_bet');
                });
            }else{
                TempAddScore::truncate();
            }
            //填充临时表数据
            foreach (array_chunk($temp_arr, 500) as $items) {
                DB::table(TempAddScore::tableName())->insert($items);
            }
            //金币添加
            DB::update("update a set a.Score = a.Score + b.add_gold from WHQJTreasureDB.dbo.GameScoreInfo as a inner join admin_platform.dbo.temp_add_score as b on a.UserID=b.user_id");
            //修改稽核打码
            DB::update("update a set a.AuditBetScore = a.AuditBetScore + b.add_gold from WHQJTreasureDB.dbo.UserAuditBetInfo as a inner join admin_platform.dbo.temp_add_score as b on a.UserID=b.user_id");
            DB::insert("INSERT INTO WHQJTreasureDB.dbo.UserAuditBetInfo (UserID,AuditBetScore) SELECT user_id,audit_bet FROM admin_platform.dbo.temp_add_score WHERE user_id NOT IN (SELECT UserID FROM WHQJTreasureDB.dbo.UserAuditBetInfo)");
            //流水记录
            foreach (array_chunk($info, 100) as $items) {
                DB::connection('record')->table(RecordTreasureSerial::tableName())->insert($items);
            }
            //删除临时表
            \Schema::dropIfExists('temp_add_score');
            DB::connection('treasure')->commit();
            DB::connection('record')->commit();
            DB::commit();
            //发送通知
            AllGiveGold::dispatch($arr);
            GameControlLog::addOne('批量上分', '给玩家批量上分', GameControlLog::USER_SCORE_UP,GameControlLog::SUCCESS);
            return ResponeSuccess('批量上分成功');
        }catch (Exception $e){
            DB::connection('treasure')->rollback();
            DB::connection('record')->rollback();
            DB::rollBack();
            \Schema::dropIfExists('temp_add_score');
            GameControlLog::addOne('批量上分', '给玩家批量上分', GameControlLog::USER_SCORE_UP,GameControlLog::FAILS);
            return ResponeFails('批量上分失败');
        }
    }
}
