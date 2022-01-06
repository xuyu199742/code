<?php
/* 注册赠送 */
namespace Modules\Activity\Http\Controllers;

use App\Exceptions\NewException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\Activity\ActivitiesNormal;
use Models\AdminPlatform\RegisterGive;
use Modules\Activity\Http\Requests\AnswerGiveSaveRequest;
use PHPUnit\Exception;
use Validator;

class ActivitiesNormalController extends Controller
{
    /**
     * 注册赠送列表
     * @return Response
     */
    public function getActivity($pid)
    {
        try {
            if (!in_array($pid, ActivitiesNormal::PIDS)) return ResponeFails("参数有误");
            $res = ActivitiesNormal::where('pid', $pid)->select('content', 'status', 'btime', 'etime')->get()->toArray();
            $data = [];
            if($res) {
                $data = json_decode($res[0]['content'], true);
                foreach ($data['detail'] as &$v) {
                    $v['money'] = floor($v['money'] / realRatio());
                    $v['score'] = floor($v['score'] / realRatio());
                }
                $data['status'] = $res[0]['status'];
            }
            return ResponeSuccess("Success", $data);
        } catch (Exception $e) {
            \Log::error("[常规活动-{$pid}]{$e}");
            return ResponeFails('操作失败');
        }
    }
    /**
     * 常规活动添加/编辑
     * @return Response
     */
    public function saveActivity(Request $request, $pid)
    {
        try {
            //生成注册赠送记录
            Validator::make($request->all(), [
                'days' => ['required', 'integer','min:1'],
                'detail' => ['required', 'array'],
                'status'  => ['required', 'in:0,1']
            ], [
                'days.required' => '天数不能为空',
                'days.integer' => '天数只能为数字',
                'days.min' => '天数最少为1天',
                'detail.required' => '活动内容不能为空',
                'detail.array' => '参数有误',
                'status.required' => '参数有误',
                'status.in' => '参数有误',
            ])->validate();

            if (!in_array($pid, ActivitiesNormal::PIDS)) {
                throw new NewException("活动不存在");
            }

            $data['days'] = $request['days'];
            $data['detail'] = $request['detail']; // 金币
            foreach ($data['detail'] as &$v) {
                $v['money'] *= realRatio();
                $v['score'] *= realRatio();
            }
            $status = $request['status'];
            $content = json_encode($data);

            $res = ActivitiesNormal::where('pid', $pid)->update(['content' => $content,'status'=>$status]);
            if($res) {
                return ResponeSuccess('操作成功');
            } else {
                return ResponeFails('操作失败');
            }
        } catch (NewException $e) {
            return ResponeFails($e->getMessage());
        } catch (Exception $e) {
            \Log::error("[常规活动-{$pid}]{$e}");
            return ResponeFails('操作失败');
        }
    }

    /**
     * 获取答题活动配置
     */
    public function getAnswerGive(){
        $data = ActivitiesNormal::AnswerGiveConfig(false);
        return ResponeSuccess('请求成功',$data);
    }

    /**
     * 答题活动保存
     */
    public function answerGiveSave(AnswerGiveSaveRequest $request){
        $input = $request->except('status');
        $status = request('status');
        $input['answer_min'] = moneyToCoins($input['answer_min']);
        $input['answer_max'] = moneyToCoins($input['answer_max']);
        $input['show_min'] = moneyToCoins($input['show_min']);
        $input['show_max'] = moneyToCoins($input['show_max']);
        try {
            $res = ActivitiesNormal::query()
                ->updateOrCreate([
                    'pid'       => ActivitiesNormal::ANSWER_ACTIVITY,
                ],[
                    'content'   => json_encode($input,true),
                    'status'    => $status
                ]);
            if($res) {
                return ResponeSuccess('操作成功');
            } else {
                return ResponeFails('操作失败');
            }
        }catch (\Exception $e){
            \Log::error("答题活动编辑失败-{$e}");
            return ResponeFails('操作失败');
        }
    }

}
