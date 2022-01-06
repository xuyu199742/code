<?php
/* 活动配置—红包配置 */
namespace Modules\Activity\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Models\Activity\ActivityConfig;
use Models\Activity\RedPacketConfig;
use Validator;

class RedPacketController extends Controller
{
    /*红包配置列表*/
    public function  red_packet_list()
    {
        $list = RedPacketConfig::get();
        foreach ($list as $k=>$v)
        {
            $list[$k]['ScoreSmall']=realCoins($list[$k]['ScoreSmall']);
            $list[$k]['ScoreBig']=realCoins($list[$k]['ScoreBig']);
        }
        return ResponeSuccess('请求成功', $list);
    }
    /*
     * 红包奖励配置保存
     * */
    public function red_packet_save(Request $request)
    {
        Validator::make($request->all(), [
            'CountdownTime'     => ['required','numeric','min:1'],
            'ScoreSmall'        => ['required','numeric','min:1'],
            'ScoreBig'          => ['required','numeric','min:1'],
        ], [
            'CountdownTime.required'   => '倒计时时长必填',
            'CountdownTime.numeric'    => '倒计时时长必须是数字',
            'CountdownTime.min'        => '倒计时时长值必须大于0',
            'ScoreSmall.required'      => '奖励'.config('set.amount').'小值必填',
            'ScoreSmall.numeric'       => '奖励'.config('set.amount').'小值必须是数字',
            'ScoreSmall.min'           => '奖励'.config('set.amount').'小值必须大于0',
            'ScoreBig.required'        => '奖励'.config('set.amount').'大值必传',
            'ScoreBig.numeric'         => '奖励'.config('set.amount').'大值必须是数字',
            'ScoreBig.min'             => '奖励'.config('set.amount').'大值必须大于0',
        ])->validate();
        if ($request->input('id')) {
            $model = RedPacketConfig::find($request->input('id'));
            if (!$model) {
                return ResponeFails('红包奖励配置不存在');
            }
        } else {
            $model = new RedPacketConfig();
            $record_num  = $model->count('id');
            if($record_num >= RedPacketConfig::RECORD_NUM)
            {
                return ResponeFails('红包个数配置不正确');
            }
            $model -> OrderID = $record_num+1;
        }
        $model -> CountdownTime  = $request->input('CountdownTime');
        $model -> ScoreSmall     = moneyToCoins($request->input('ScoreSmall'));
        $model -> ScoreBig       = moneyToCoins($request->input('ScoreBig'));
        if ($model->save()) {
            //activityInform();
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }
    /*
     * 红包奖励配置删除
     * */
    /*public function red_packet_delete(Request $request)
    {
        $id = $request->input('id');
        if(!$id){
            return ResponeFails('id必传');
        }
        $model=RedPacketConfig::find($id);
        if(!$model){
            return ResponeFails('红包奖励配置不存在');
        }
        $res =  $model->where('id', $id)->delete();
        if ($res) {
            return ResponeSuccess('删除成功');
        }
        return $this->response->errorInternal('删除失败');
    }*/
    /*
   * 红包功能设置列表
   *
   */
    public function  red_packet_effect_list(Request $request)
    {
        $list=ActivityConfig::where('TurntableType',ActivityConfig::RED_PACKET_TYPE)->get();
        foreach ($list  as $k=>$v)
        {
            $list[$k]['BigWinRangeStart']=realCoins($list[$k]['BigWinRangeStart']);
        }
        return ResponeSuccess('请求成功', $list);
    }

    /*
     * 红包功能设置
     * */
    public function red_packet_effect_save(Request $request)
    {
        Validator::make($request->all(), [
            'StartTime'          => ['required','date'],
            'EndTime'            => ['required','date'],
            'BigWinNoticeStart'  => ['required','numeric','min:1'],
            'BigWinNoticeEnd'    => ['required','numeric','min:1'],
            'BigWinRangeStart'   => ['required','numeric','min:1'],
            'BigWinRangeEnd'     => ['required','numeric','min:1'],
            'Describe'           => ['required','max:225'],

        ], [
            'StartTime.required'         => '开始时间必填',
            'EndTime.required'           => '结束时间必填',
            'BigWinNoticeStart.numeric'  => '大奖公告次数下限必须是数字',
            'BigWinNoticeStart.min'      => '大奖公告次数下限值必须大于0',
            'BigWinNoticeEnd.numeric'    => '大奖公告次数上限必须是数字',
            'BigWinNoticeEnd.min'        => '大奖公告次数上限值必须大于0',
            'BigWinRangeStart.numeric'   => '大奖范围下限必须是数字',
            'BigWinRangeStart.min'       => '大奖范围下限值必须大于0',
            'BigWinRangeEnd.numeric'     => '大奖范围上限必须是数字',
            'BigWinRangeEnd.min'         => '大奖范围上限值必须大于0',
            'Describe.required'          => '规则描述必填',
            'Describe.max'               => '规则描述不超过225个字符',
        ])->validate();
        if ($request->input('id')) {
            $model = ActivityConfig::find($request->input('id'));
            if (!$model) {
                return ResponeFails('红包奖励配置不存在');
            }
        } else {
            $model = new ActivityConfig();
        }
        $model -> StartTime          = $request->input('StartTime');
        $model -> EndTime            = $request->input('EndTime');
        $model -> OpenCondition      = 0;
        $model -> TurntableType      = ActivityConfig::RED_PACKET_TYPE;
        $model -> BigWinNoticeStart  = $request->input('BigWinNoticeStart');
        $model -> BigWinNoticeEnd    = $request->input('BigWinNoticeEnd');
        $model -> BigWinRangeStart   = moneyToCoins($request->input('BigWinRangeStart'));
        $model -> BigWinRangeEnd     = moneyToCoins($request->input('BigWinRangeEnd'));
        $model -> Describe           = $request->input('Describe');
        if ($model->save()) {
            //activityInform();
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }
}
