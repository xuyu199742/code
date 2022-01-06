<?php

namespace Modules\Activity\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\Activity\Activity;
use Models\Activity\RankingAwardConfig;
use Models\Activity\RankingConfig;
use Models\Activity\RankingRecord;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
use Modules\Activity\Http\Requests\RankingConfigRequest;
use Transformers\RankingConfigTransformer;
use Transformers\RankingRecordTransformer;

class RankingController extends Controller
{
    /**
     * 排行活动设置时间
     *
     */
    public function setTime()
    {
        if (request()->isMethod('get')){
            $Activity = Activity::where('type', Activity::RANKING_ACTIVITY)->first();
            return ResponeSuccess('获取成功',$Activity);
        }elseif (request()->isMethod('post')){
            \Validator::make(request()->all(), [
                'start_time'    => ['required', 'date'],
                'end_time'      => ['required', 'date'],
            ], [
                'start_time.required'   => '开始时间必填',
                'start_time.date'       => '开始时间格式有误',
                'end_time.required'     => '结束时间必填',
                'end_time.date'         => '结束时间格式有误',
            ])->validate();
            $start_time = request('start_time');
            $end_time = date('Y-m-d 23:59:59',strtotime(request('end_time')));
            if(strtotime($start_time) > strtotime($end_time)) {
                return ResponeFails('结束时间需大于开始时间');
            }
            $Activity = Activity::where('type', Activity::RANKING_ACTIVITY)->first();
            if (!$Activity){
                $Activity         = new Activity();
                $Activity->type   = Activity::RANKING_ACTIVITY;
            }
            $Activity->start_time = $start_time;
            $Activity->end_time   = $end_time;
            if ($Activity->save()) {
                return ResponeSuccess('保存成功');
            }
            return ResponeFails('保存失败');
        }
    }

    /**
     * 排行活动列表
     *
     */
    public function getList()
    {
        \Validator::make(request()->all(), [
            'rank_id'       => 'nullable|integer',
            'server_id'     => 'nullable|integer',
            'type'          => 'nullable|in:1,2',
        ], [
            'rank_id.integer'    => '游戏id为整型',
            'server_id.integer'  => '房间id为整型',
            'type.in'            => '类型不在范围内',
        ])->validate();
        $list = RankingConfig::from('RankingConfig as a')
            ->select(
                \DB::raw("(select sum(score) from ".RankingRecord::tableName()." where rank_id=a.id) as score"),
                'a.id','a.type','a.start_time','end_time','b.KindName','c.ServerName'
            )
            ->leftJoin(GameKindItem::tableName().' as b','a.kind_id','=','b.KindID')
            ->leftJoin(GameRoomInfo::tableName().' as c','a.server_id','=','c.ServerID')
            ->whereNull('a.deleted_at')
            ->withTrashed()
            ->andFilterWhere('a.kind_id', request('kind_id'))
            ->andFilterWhere('a.server_id', request('server_id'))
            ->andFilterWhere('a.type', request('type'))
            ->paginate(config('a.page.list_rows'));
        return $this->response->paginator($list,new RankingConfigTransformer());
    }

    /**
     * 新增排行活动
     *
     */
    public function add(RankingConfigRequest $request)
    {
        try{
            $awards = request('awards');
            foreach ($awards as $k => $v){
                if (!is_numeric( $v['place'] )){
                    return ResponeFails('名次必须是数字');
                }
                if ($v['place'] < 1 ){
                    return ResponeFails('名次最小为1');
                }
                if (!is_numeric( $v['score'] )){
                    return ResponeFails('奖励'.config('set.amount').'必须是数字');
                }
                if ($v['score'] < 0 ){
                    return ResponeFails('奖励'.config('set.amount').'必须大于0');
                }
                $index=$k-1;
                if($index>=0){
                    $award[$k] = ($awards[$k]['place'] - $awards[$index]['place'])* (int)$awards[$k]['score'];
                }else{
                    $award[$k] = (int)$awards[$k]['place'] * (int)$awards[$k]['score'];
                }
            }
            $sum_awards = array_sum($award);
            $Activity = Activity::where('type', Activity::RANKING_ACTIVITY)->first();
            if (!$Activity){
                return ResponeFails('请先配置好活动时间');
            }
            //转换时间
            $stime = timesTransform(request('start_time'));
            $etime = timesTransform(request('end_time'));
            if ($stime > $etime){
                return ResponeFails('开始时间不能大于结束时间');
            }
            $rank_list = RankingConfig::select('start_time','end_time','type')
                ->where('kind_id',request('kind_id'))
                ->where('server_id',request('server_id'))
                ->get();
            foreach ($rank_list as $k => $v){
                //同一个游戏的房间只能配置一种类型的
                if ($v->type != request('type')){
                    return ResponeFails('同一游戏房间只能配置一种类型的');
                }
                //同一种类型的可以有多个时间段的，但不能有重复时间段的
                if ($stime < $v['end_time'] && $etime > $v['start_time']){
                    return ResponeFails('同类型活动不能具有重复时间');
                }
            }
            $Ranking = new RankingConfig();
            $Ranking->activity_id      = $Activity->id;
            $Ranking->kind_id          = request('kind_id');
            $Ranking->server_id        = request('server_id');
            $Ranking->type             = request('type');
            $Ranking->min_times        = request('min_times');
            $Ranking->min_activity_num = request('min_activity_num');
            $Ranking->min_award_num    = request('min_award_num');
            $Ranking->start_time       = $stime;
            $Ranking->end_time         = $etime;
            $Ranking->sum_awards       = moneyToCoins($sum_awards);
            $Ranking->save();
            $info = [];
            foreach ($awards as $k => $v){
                $info[$k]['rank_id']        = $Ranking->id;
                $info[$k]['place']          = $v['place'];
                $info[$k]['score']          = moneyToCoins($v['score']);
            }
            RankingAwardConfig::insert($info);
            return ResponeSuccess('保存成功');
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 编辑排行活动
     *
     */
    public function edit(RankingConfigRequest $request,$id)
    {
        try{
            $Ranking = RankingConfig::find($id);
            if (empty($Ranking)){
                return ResponeFails('活动不存在');
            }
            if (request()->isMethod('get')){
                $awards =RankingAwardConfig::select('place','score')->where('rank_id',$Ranking->id)->get();
                foreach ($awards as $k => $v){
                    $awards[$k]['score'] = realCoins($v->score);
                }
                $Ranking->awards = $awards;
                //时间转换
                $Ranking->start_time    = secondTransform($Ranking->start_time);
                $Ranking->end_time      = secondTransform($Ranking->end_time);
                return ResponeSuccess('请求成功',$Ranking);
            }elseif (request()->isMethod('post')){
                $awards = request('awards');
                foreach ($awards as $k => $v){
                    if (!is_numeric( $v['place'] )){
                        return ResponeFails('名次必须是数字');
                    }
                    if ($v['place'] < 1 ){
                        return ResponeFails('名次最小为1');
                    }
                    if (!is_numeric( $v['score'] )){
                        return ResponeFails('奖励'.config('set.amount').'必须是数字');
                    }
                    if ($v['score'] < 0 ){
                        return ResponeFails('奖励'.config('set.amount').'必须大于0');
                    }
                    $index=$k-1;
                    if($index>=0){
                        $award[$k] = ($awards[$k]['place'] - $awards[$index]['place'])* (int)$awards[$k]['score'];
                    }else{
                        $award[$k] = (int)$awards[$k]['place'] * (int)$awards[$k]['score'];
                    }
                }
                $sum_awards = array_sum($award);
                //转换时间
                $stime = timesTransform(request('start_time'));
                $etime = timesTransform(request('end_time'));
                if ($stime > $etime){
                    return ResponeFails('开始时间不能大于结束时间');
                }
                $rank_list = RankingConfig::select('start_time','end_time','type')
                    ->where('kind_id',request('kind_id'))
                    ->where('server_id',request('server_id'))
                    ->where('id','<>',$id)
                    ->get();
                foreach ($rank_list as $k => $v){
                    //同一个游戏的房间只能配置一种类型的
                    if ($v->type != request('type')){
                        return ResponeFails('同一游戏房间只能配置一种类型的');
                    }
                    //同一种类型的可以有多个时间段的，但不能有重复时间段的
                    if ($stime < $v['end_time'] && $etime > $v['start_time']){
                        return ResponeFails('同类型活动不能具有重复时间');
                    }
                }
                $Ranking->kind_id          = request('kind_id');
                $Ranking->server_id        = request('server_id');
                $Ranking->type             = request('type');
                $Ranking->min_times        = request('min_times');
                $Ranking->min_activity_num = request('min_activity_num');
                $Ranking->min_award_num    = request('min_award_num');
                $Ranking->start_time       = $stime;
                $Ranking->end_time         = $etime;
                $Ranking->sum_awards       = moneyToCoins($sum_awards);
                $Ranking->save();
                RankingAwardConfig::where('rank_id',$Ranking->id)->delete();
                $info = [];
                foreach ($awards as $k => $v){
                    $info[$k]['rank_id']        = $Ranking->id;
                    $info[$k]['place']          = $v['place'];
                    $info[$k]['score']          =  moneyToCoins($v['score']);
                }
                RankingAwardConfig::insert($info);
                return ResponeSuccess('保存成功');
            }
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 删除排行活动
     *
     */
    public function del($id)
    {
        if (RankingConfig::destroy($id)) {
            return ResponeSuccess('删除成功');
        } else {
            return ResponeFails('删除失败或已被删除');
        }
    }

    /**
     * 获取游戏及类型
     *
     */
    public function getKindType()
    {
        $list = GameKindItem::with(['rooms'=>function($query){
            //去除体验场
            $query->where('ServerLevel','<>',1)->select('KindID','ServerID','ServerName');
        }])
            ->whereNotIn('KindID',[522,523,524,525,528])//去除不需要的游戏
            ->select('KindID','KindName')
            ->get();
        return ResponeSuccess('请求成功',$list);
    }

    /**
     * 详情
     *
     */
    public function details()
    {
        \Validator::make(request()->all(), [
            'rank_id'     => 'required|integer',
            'date'        => 'required|date',
            'game_id'     => 'nullable|integer',
        ], [
            'rank_id.required'   => '活动id必传',
            'rank_id.integer'    => '活动id为整型',
            'date.required'      => '日期必须选择',
            'date.date'          => '日期格式有误',
            'game_id.integer'    => '用户id为整型',
        ])->validate();
        $list = RankingRecord::from('RankingRecord as a')
            ->select('a.id','a.sort','a.water','a.bet','a.num','a.score','b.GameID')
            ->leftJoin(AccountsInfo::tableName().' as b','a.user_id','=','b.UserID')
            ->where('a.rank_id',request('rank_id'))
            ->whereDate('a.created_at',date("Y-m-d",strtotime("+1 day",strtotime(request('date')))))
            ->andFilterWhere('b.GameID',request('game_id'))
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new RankingRecordTransformer());
    }
}
