<?php
/* 任务活动 */
namespace Modules\Activity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Accounts\AccountsInfo;
use Models\Activity\Activity;
use Models\Activity\ActivityConfig;
use Models\Activity\ActivityTaskConfig;
use Models\Activity\TaskRecord;
use Models\Platform\GameKindItem;

use Models\Platform\GameRoomInfo;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Transformers\ActivityTaskConfigTransformer;
use Transformers\GameKindItemTransformer;
use Transformers\TaskRecordTransformer;
use Validator;


class ActivityTaskController extends Controller
{

    /**
     * 任务活动配置列表
     *
     */
    public function getTaskActivityList()
    {
        $list = ActivityTaskConfig::from(ActivityTaskConfig::tableName().' as a')
            ->leftJoin(GameKindItem::tableName().' as b','a.kind_id','=','b.KindID')
            ->leftJoin(GameRoomInfo::tableName().' as c','a.server_id','=','c.ServerID')
            ->select('a.*','b.KindID','b.KindName','c.ServerID','c.ServerName')
            ->whereNull('a.deleted_at')
            ->withTrashed()
            ->andFilterWhere('a.kind_id',request('kind_id'))
            ->andFilterWhere('a.server_id',request('server_id'))
            ->orderBy('a.created_at','desc')->paginate(10);
        foreach ($list as $k=>$v)
        {
            $list[$k]['reward_record'] = TaskRecord::select(
                \DB::raw('SUM(score) as score'),    //活动发放礼金
                \DB::raw('COUNT(*) as times')    //奖励领取次数
            )->where('task_id',$v['id'])->first();
        }
        return $this->response->paginator($list,new ActivityTaskConfigTransformer());
    }
    /**
     * 活动时间配置显示
     *
     */
    public function taskActivityTimeShow(Request $request)
    {
        $res = Activity::where('type',Activity::TASK_ACTIVITY)->first();
        $list=[];
        if($res){
            $list=[
                'id'           => $res['id'],
                'type'         => $res['type'],
                'start_time'   => date('Y-m-d H:i:s', strtotime($res['start_time'])),
                'end_time'     => date('Y-m-d H:i:s', strtotime($res['end_time'])),
                'status'       => $res['status'],
            ];
        }
        return ResponeSuccess('请求成功', $list);
    }
    /**
     * 活动时间配置
     *
     */
    public function taskActivityTimeConfig(Request $request)
    {
        Validator::make(request()->all(), [
            'start_time'    => ['required', 'date'],
            'end_time'      => ['required', 'date'],
        ], [
            'start_time.required'  => '开始时间必填',
            'end_time.required'    => '结束时间必填',
        ])->validate();
        $start_time = request('start_time');
        $end_time = request('end_time');
        if(strtotime($start_time) > strtotime($end_time))
        {
            return ResponeFails('结束时间需大于开始时间');
        }
        if ($request->input('id')) {    //修改
            $model = Activity::find($request->input('id'));
            if (!$model) {
                return ResponeFails('活动配置不存在');
            }
        } else {
            $model = new Activity();
            $is_exit = $model->where('type',Activity::TASK_ACTIVITY)->first();
            if($is_exit)
            {
                return ResponeFails('该活动已存在');
            }
        }
        $model -> type          = Activity::TASK_ACTIVITY;
        $model -> start_time    = $start_time;
        $model -> end_time      = $end_time;
        if ($model->save()) {
            //activityInform();
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }
    /**
     * 任务活动配置详情
     *
     */
    public function taskConfigDetails()
    {
        Validator::make(request()->all(), [
            'id'    => ['required'],
        ], [
            'id.required' => '任务活动配置id必传'
        ])->validate();
        $res = ActivityTaskConfig::from(ActivityTaskConfig::tableName().' as a')
            ->leftJoin(GameKindItem::tableName().' as b','a.kind_id','=','b.KindID')
            ->leftJoin(GameRoomInfo::tableName().' as c','a.server_id','=','c.ServerID')
            ->select('a.*','b.KindID','b.KindName','c.ServerID','c.ServerName')
            ->whereNull('a.deleted_at')
            ->withTrashed()->where('a.id',request('id'))->first();
        if(!$res){
            return ResponeFails('该任务活动配置不存在或已被删除');
        }
        if($res['is_cycle'] == 1){  //循环任务
            $task_attribute = 2;
        }else{
            if($res['cycle_day'] > 0){
                $task_attribute = 4; //自定义任务
            }else{
                if($res['task_num'] > 1){
                    $task_attribute = 1; //多次任务
                }else{
                    $task_attribute = 3; //单次任务
                }
            }
        }
        $data = [
            'id'             => $res['id'],
            'kind_id'        => $res['kind_id'],
            'kind_name'      => $res['KindName'],
            'server_id'      => $res['server_id'],
            'server_name'    => $res['ServerName'],
            'category'       => ActivityTaskConfig::MATCHES_NUM,
            'condition'      => $res['condition'],
            'reward'         => intval(realCoins($res['reward'])),
            'task_attribute' => $task_attribute,
            'cycle_day'      => $res['cycle_day'],
            'task_num'       => $res['task_num'],
            'nullity'        => $res['nullity'],
        ];
        return ResponeSuccess('请求成功', $data);

    }
    /**
     * 任务活动配置新增/编辑
     *
     */
    public function taskActivityConfig(Request $request)
    {
        Validator::make(request()->all(), [
            'kind_id'       => ['required', 'numeric'],
            'server_id'     => ['required', 'numeric'],
            'condition'     => ['required','numeric','min:1'],
            'reward'        => ['required','numeric','min:1'],
            'cycle_day'     => ['nullable','numeric'],
            'task_num'      => ['nullable','numeric','min:1'],
            'nullity'       => ['required','in:0,1'],//开启状态
            'task_attribute'=> ['required','in:1,2,3,4'] //任务属性
        ], [
            'kind_id.required'         => '游戏类型必选',
            'server_id.required'       => '房间类型必选',
            'condition.required'       => '条件必填',
            'condition.min'            => '条件值必须大于0',
            'reward.required'          => '奖励金币必填',
            'reward.min'               => '奖励金币值必须大于0',
            'task_num.min'             => '次数必须大于0',
            'nullity.required'         => '开启状态设置',
            'nullity.in'               => '开启状态不在可选范围内',
            'task_attribute.required'  => '任务属性必选',
            'task_attribute.in'        => '任务属性不在可选范围内',
        ])->validate();
        $is_exit_activity = Activity::where('type',Activity::TASK_ACTIVITY)->first();
        if(!$is_exit_activity)
        {
            return ResponeFails('请先配置任务活动的开始时间和结束时间');
        }
        $task_attribute = $request->input('task_attribute');
        if ($request->input('id')) {    //修改
            $model = ActivityTaskConfig::find($request->input('id'));
            if (!$model) {
                return ResponeFails('任务活动配置不存在');
            }
        } else {
            $model = new ActivityTaskConfig();
            $category = ActivityTaskConfig::MATCHES_NUM;
            $is_exit  = $model-> where('kind_id',$request->input('kind_id'))
                ->where('server_id',$request->input('server_id'))->where('category',$category)->first();
            if($is_exit){
                return ResponeFails('该任务活动配置已存在');
            }
        }
        $model -> activity_id   = $is_exit_activity['id'];
        $model -> kind_id       = $request->input('kind_id');
        $model -> server_id     = $request->input('server_id');
        $model -> category      = ActivityTaskConfig::MATCHES_NUM;
        $model -> condition     = $request->input('condition');
        $model -> reward        = moneyToCoins($request->input('reward'));
        if($task_attribute == 2){
            $model -> is_cycle  = 1;//循环任务
            $model -> cycle_day = 1;//一天
            $model -> task_num  = 1;//一次
        }else{
            $model -> is_cycle  = 0; //不循环
            if($task_attribute == 3){ //单次任务
                $model -> cycle_day = 0;
                $model -> task_num  = 1;
            }elseif ($task_attribute == 1){  //多次任务
                $model -> cycle_day = 0;
                $model -> task_num  = $request->input('task_num');
            }else{
                $model -> cycle_day = $request->input('cycle_day');
                $model -> task_num  = $request->input('task_num');
            }
        }
        $model -> nullity      = $request->input('nullity');
        if ($model->save()) {
            //activityInform();
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }
    /**
     * 任务活动-用户领取记录
     *
     */
    public function taskActivityRecord()
    {
        Validator::make(request()->all(), [
            'id'            => ['required'],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date'],
        ], [
            'id.required'      => '任务活动配置id必传',
            'start_date.date'  => '无效日期',
            'end_date.date'    => '无效日期',
        ])->validate();
        $list = TaskRecord::select('user_id',\DB::raw('MAX(reward_time) as reward_time'))
            ->where('task_id',request('id'))
            ->andFilterBetweenWhere('reward_time', request('start_date'), request('end_date'))
            ->groupBy('user_id')->paginate(config('page.list_rows'));
        foreach ($list as $k=>$v)
        {
            //当前输赢改为：当前玩家输赢=中奖-投注
           /* $list[$k]['game_data']=AccountsInfo::from(AccountsInfo::tableName().' as a')
                ->leftJoin(GameScoreInfo::tableName().' as b','a.UserID','=','b.UserID')
                ->select('a.Accounts','a.GameID','b.WinScore','b.JettonScore' )
                ->where('a.UserID',$v['user_id'])->first();*/
	        $list[$k]['game_data']=AccountsInfo::from(AccountsInfo::tableName().' as a')
                 ->where('a.UserID',$v['user_id'])->first();
            $list[$k]['reward_score'] = RecordGameScore::select(
                \DB::raw('SUM(RewardScore) as score'),
                \DB::raw('SUM(RewardScore-JettonScore) as user_win_lose'),  //当前玩家输赢=中奖-投注
                \DB::raw('SUM(JettonScore-RewardScore) as payout')   //派彩 = 投注-中奖
            )->where('UserID',$v['user_id'])->first();
            $list[$k]['receive_record'] = TaskRecord::select(
                \DB::raw('SUM(score) as score'),
                \DB::raw('COUNT(*) as times')
            )->where('task_id',request('id'))->where('user_id',$v['user_id'])
                ->andFilterBetweenWhere('reward_time', request('start_date'), request('end_date'))->first();
        }
        return $this->response->paginator($list,new TaskRecordTransformer());
    }
    /**
     * 任务活动-删除任务活动配置
     *
     */
    public function taskConfigDelete($id)
    {
        $task_activity = ActivityTaskConfig::find($id);
        if ($task_activity) {
            $task_activity->delete();
            return ResponeSuccess('删除成功');
        } else {
            return ResponeFails('该任务活动不存在');
        }
    }
    /*
     * 任务活动-选择游戏和房间
     *
     * */
    public function gameJoinRoom()
    {
        $arr_kind_type = ['522', '523'];//李逵劈鱼和3d捕鱼
        $list = GameKindItem::from(GameKindItem::tableName().' as a')
            ->leftJoin(GameRoomInfo::tableName().' as b','a.KindID','=','b.KindID')
            ->select('a.KindID','a.KindName','b.ServerID','b.ServerName','b.ServerLevel')
            ->whereNotIn('a.KindID', $arr_kind_type)
            ->where('b.ServerLevel','<>',1)->get();
        $res = collect($list->toArray())->groupBy('KindName');
        return ResponeSuccess('请求成功', $res);
    }
    /*
    * 任务，返利，排行活动开关
    *
    * */
    public function setActivityStatus()
    {
        Validator::make(request()->all(), [
            'type'       => ['required','in:0,1,2,3'],
            'status'     => ['required','in:0,1'],
            'table_type' => ['required','in:1,2'],
        ], [
            'type.required'    => '活动类型必传',
            'type.in'          => '活动类型不在可选范围内',
            'status.in'        => '开关状态值不在可选范围内',
        ])->validate();
        if(request('table_type') == 1){  //转盘和红包
            $res = ActivityConfig::where('TurntableType',request('type'))->update(['status' => request('status')]);
        }else{    //任务，返利，排行活动
            $res = Activity::where('type',request('type'))->update(['status' => request('status')]);
        }
        if($res){
            return ResponeSuccess('修改成功');
        } else {
            return ResponeFails('修改失败');
        }
    }
}
