<?php

namespace Modules\Activity\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\Activity\Activity;
use Models\Activity\ActivityReturnConfig;
use Models\Activity\ReturnRateConfig;
use Models\Activity\ReturnRecord;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Modules\Activity\Http\Requests\AcitvityRebateRequest;
use Transformers\ActivityRebateTransformer;
use Transformers\ActivityReturnRecordTransformer;

class RebateConfigController extends Controller
{
    /**
     * 返利活动列表
     *
     */
    public function rebateActList()
    {
        \Validator::make(request()->all(), [
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
        ], [
            'start_date.date'   => '日期格式有误',
            'end_date.date'     => '日期格式有误',
        ])->validate();
        $start_date = request('start_date');
        $end_date   = request('end_date');
        if (empty($start_date) && !empty($end_date)){
            return ResponeFails('开始时间必填');
        }
        if (!empty($start_date) && empty($end_date)){
            return ResponeFails('结束时间必填');
        }
        try{
            $list = ActivityReturnConfig::from('ActivityReturnConfig as a')
                ->with('logs')
                ->select('a.*','b.start_time','b.end_time','b.status')
                ->leftJoin(Activity::tableName().' as b','a.activity_id','=','b.id')
                ->whereNull('a.deleted_at')
                ->withTrashed()
                ->andFilterWhere('b.start_time', '<=', $end_date)
                ->andFilterWhere('b.end_time', '>=', $start_date)
                ->paginate(config('page.list_rows'));
            foreach ($list as $k => $v){
                $list[$k]['activity_score'] = $v->logs->sum('score');
            }
            return $this->response->paginator($list,new ActivityRebateTransformer());
        }catch (\Exception $exception){
            //dd($exception->getMessage());
            return ResponeFails('异常错误');
        }
    }

    /**
     * 返利活动新增
     *
     */
    public function rebateActAdd(AcitvityRebateRequest $request)
    {
        try{
            $weal = request('weal');
            foreach ($weal as $k => $v){
                if (!is_numeric( $v['score'] )){
                    return ResponeFails('福利配置数值必须是数字');
                }
                if ($v['score'] < 0 ){
                    return ResponeFails('福利配置数值必须大于0');
                }
                if ($v['rate'] <= 0 ){
                    return ResponeFails('返利金币必须大于0');
                }
            }
            //同一时间范围中不能有相同类型的活动
            $act_list = ActivityReturnConfig::from('ActivityReturnConfig as a')
                ->select('b.start_time','b.end_time')
                ->leftJoin(Activity::tableName().' as b','a.activity_id','=','b.id')
                ->whereNull('a.deleted_at')
                ->withTrashed()
                ->where('category',request('category'))
                ->get();
            foreach ($act_list as $k => $v){
                if (strtotime(request('start_time')) < strtotime($v['end_time']) && strtotime(request('end_time')) > strtotime($v['start_time'])){
                    return ResponeFails('同类型活动不能具有重复时间');
                }
            }
            //添加返利活动
            $Activity             = new Activity();
            $Activity->type       = 2;//活动类型
            $Activity->start_time = request('start_time', date('Y-m-d H:i:s'));//开始时间
            $Activity->end_time   = request('end_time', date('Y-m-d H:i:s'));//结束时间
            $Activity->save();
            //添加返利活动关联的子活动
            $ActivityRate              = new ActivityReturnConfig();
            $ActivityRate->activity_id = $Activity->id;
            $ActivityRate->name        = request('name');
            $ActivityRate->category    = request('category',ActivityReturnConfig::WATER);
            $ActivityRate->nullity     = request('nullity',ActivityReturnConfig::NULLITY_ON);
            $ActivityRate->img_address = request('img_address') ?? '';
            $ActivityRate->save();
            //添加子活动配置
            foreach ($weal as $k => $v){
                $weal[$k]['activity_id']        = $Activity->id;
                $weal[$k]['score']              = $v['score'] * 10000;
                $weal[$k]['rate']               = $v['rate'] * 10000;
                $weal[$k]['activity_return_id'] = $ActivityRate->id;
            }
            ReturnRateConfig::insert($weal);
            return ResponeSuccess('保存成功');
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 返利活动编辑
     *
     */
    public function rebateActEdit(AcitvityRebateRequest $request,$id)
    {
        try{
            //查询活动
            $ActivityRate = ActivityReturnConfig::find($id);
            if (empty($ActivityRate)){
                return ResponeFails('活动不存在');
            }
            if (request()->isMethod('get')){
                $Activity = ActivityReturnConfig::from('ActivityReturnConfig as a')
                    ->with('weal')
                    ->select('a.*','b.start_time','b.end_time')
                    ->leftJoin(Activity::tableName().' as b','a.activity_id','=','b.id')
                    ->where('a.id',$id)
                    ->whereNull('a.deleted_at')
                    ->withTrashed()
                    ->first();
                foreach ($Activity->weal as $k => $v){
                    $Activity->weal[$k]['score'] = intval(realCoins($v->score));
                    $Activity->weal[$k]['rate']  = realCoins($v->rate);
                }
                $Activity->all_img_address = cdn($Activity->img_address);
                return ResponeSuccess('请求成功',$Activity);
            }elseif (request()->isMethod('post')){
                $weal = request('weal');
                foreach ($weal as $k => $v){
                    if (!is_numeric( $v['score'])){
                        return ResponeFails('福利配置数值必须是整数');
                    }
                    if ($v['score'] < 0 ){
                        return ResponeFails('福利配置数值必须大于0');
                    }
                    if (!is_numeric( $v['rate'])){
                        return ResponeFails('返利比例必须是整数');
                    }
                    if ($v['rate'] <= 0 ){
                        return ResponeFails('返利金币必须大于0');
                    }
                }
                //修改返利活动
                $Activity = Activity::where('id',$ActivityRate->activity_id)->first();
                if (strtotime($Activity->end_time) <= time()){
                    return ResponeFails('当前活动已过期，不能进行编辑');
                }
                //同一时间范围中不能有相同类型的活动
                $act_list = ActivityReturnConfig::from('ActivityReturnConfig as a')
                    ->select('b.start_time','b.end_time')
                    ->leftJoin(Activity::tableName().' as b','a.activity_id','=','b.id')
                    ->where('a.category',request('category'))
                    ->where('a.id','<>',$id)
                    ->whereNull('a.deleted_at')
                    ->withTrashed()
                    ->get();
                foreach ($act_list as $k => $v){
                    if (strtotime(request('start_time')) < strtotime($v['end_time']) && strtotime(request('end_time')) > strtotime($v['start_time'])){
                        return ResponeFails('同类型活动不能具有重复时间');
                    }
                }
                Activity::where('id',$ActivityRate->activity_id)->update(['start_time'=>request('start_time'),'end_time'=>request('end_time')]);
                //修改返利活动关联的子活动
                $info['name']         = request('name');
                //$info['category']   = request('category');
                $info['nullity']      = request('nullity');
                $info['img_address']  = request('img_address') ?? '';
                ActivityReturnConfig::where('id',$id)->update($info);
                //修改子活动配置
                $ReturnRate = new ReturnRateConfig();
                $ReturnRate->where('activity_return_id',$ActivityRate->id)->where('activity_id',$ActivityRate->activity_id)->delete();
                foreach ($weal as $k => $v){
                    $weal[$k]['activity_id']        = $ActivityRate->activity_id;
                    $weal[$k]['activity_return_id'] = $ActivityRate->id;
                    $weal[$k]['score']              = $v['score'] * 10000;
                    $weal[$k]['rate']               = $v['rate'] * 10000;
                    unset($weal[$k]['id']);
                }
                $ReturnRate::insert($weal);
                return ResponeSuccess('保存成功');
            }
        }catch (\Exception $exception){
            return ResponeFails('存在非法操作');
        }
    }

    /**
     * 返利活动日志
     *
     */
    public function rebateActLog()
    {
        \Validator::make(request()->all(), [
            'activity_return_id'    => 'required|integer',
            'start_date'            => 'nullable|date',
            'end_date'              => 'nullable|date',
        ], [
            'activity_return_id.required'   => '活动id必传',
            'activity_return_id.integer'    => '活动id格式有误',
            'start_date.date'               => '无效日期',
            'end_date.date'                 => '无效日期',
        ])->validate();
        try{
            $list = ReturnRecord::from('ReturnRecord as a')
                ->select(
                    \DB::raw("sum(a.score) as score"),
                    \DB::raw("max(a.reward_time) as reward_time"),
                    \DB::raw("max(c.WinScore) as win_score"),
                    //中奖
                    \DB::raw("(select sum(RewardScore) from ".RecordGameScore::tableName()." where UserID=a.user_id ) as reward"),
                    //投注
                    \DB::raw("max(c.JettonScore) as bet"),
                    //当前玩家输赢=中奖-投注
                    \DB::raw("(select sum(RewardScore-JettonScore) from ".RecordGameScore::tableName()." where UserID=a.user_id ) as user_win_lose"),
                    //有效投注
                    //\DB::raw("(select sum(ValidJettonScore) from ".RecordGameScore::tableName()." where UserID=a.user_id ) as valid_bet"),
                    'b.GameID'
                )
                ->leftJoin(AccountsInfo::tableName().' as b','a.user_id','=','b.UserID')
                ->leftJoin(GameScoreInfo::tableName().' as c','a.user_id','=','c.UserID')
                ->where('a.activity_return_id',request('activity_return_id'))
                ->andFilterBetweenWhere('a.reward_time', request('start_date'), request('end_date'))
                ->groupBy('b.GameID','a.user_id')
                ->paginate(config('page.list_rows'));
            return $this->response->paginator($list,new ActivityReturnRecordTransformer());
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 返利活动删除
     *
     */
    public function rebateActDel($id)
    {
        if (ActivityReturnConfig::destroy($id)) {
            return ResponeSuccess('删除成功');
        } else {
            return ResponeFails('删除失败或已被删除');
        }
    }

}
