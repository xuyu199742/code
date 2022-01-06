<?php
/* 活动设置——转盘设置 */

namespace Modules\Activity\Http\Controllers;

use App\Exceptions\NewException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Models\Activity\ActivitiesNormal;
use Models\Activity\RotaryConfig;
use Models\Activity\TurntableConfig;
use Validator;


class TurntableController extends Controller
{
    /*
    * 转盘奖励配置列表
    *
    */
    public function turntable_reward_list(Request $request)
    {
        Validator::make($request->all(), [
            'pid' => ['required', 'in:3,4'],
        ], [
            'pid.required' => '转盘类型必传',
            'pid.in'       => '转盘类型不在可选范围',
        ])->validate();
        $data = [];
        $list = RotaryConfig::query()->where('pid', $request->pid)->get();

        if (!$list->count()) {
            for ($i = 1; $i <= 12; $i++) {
                foreach (ActivitiesNormal::RANK_LIST as $key => $item) {
                    $list[] = RotaryConfig::query()->create([
                        'pid'       => $request->pid,
                        'rank_type' => $key,
                        'region'    => $i,
                        'reward'    => 0,
                        'weight'    => 0,
                    ]);
                }
            }
        }
        foreach ($list->groupBy('region') as $k => $items) {
            $data[$k] = [
                'region' => $k,
            ];
            foreach (ActivitiesNormal::RANK_LIST as $key => $value) {
                $item = $items->firstWhere('rank_type', $key) ?? null;
                $data[$k] += [
                    'id'            => $item->id,
                    'reward' . $key => realCoins($item->reward),
                    'weight' . $key => $item->weight ?? '',
                ];
            }
        }

        $data = [
            'data' => collect($data)->values(),
        ];
        return ResponeSuccess('请求成功', $data);
    }

    /*
    * 转盘奖励设置
    *
    */
    public function turntable_reward_save(Request $request)
    {
        Validator::make($request->all(), [
            'pid' => ['required', 'in:3,4'],
        ], [
            'pid.required' => '转盘类型必传',
            'pid.in'       => '转盘类型不在可选范围',
        ])->validate();
        $turntableConfig = ActivitiesNormal::query()->where('pid', $request->pid)->first();
        try {
            if (!$turntableConfig) {
                return ResponeFails('转盘奖励配置不存在');
            }
//            $region = RotaryConfig::query()->where('pid', $request->pid)->selectRaw('COUNT(DISTINCT region) as count')->first()->count ?? 0;

//            if (!$request->id && !$request->region) {
//                if ($region >= TurntableConfig::RECORD_NUM) {
//                    return ResponeFails('转盘奖励配置区域数不正确');
//                }
//                $this->updateOrCreate('create', $request, $region);
//            }
            $this->updateOrCreate('update', $request);
            return ResponeSuccess('保存成功');
        } catch (\Exception $e) {
            info('转盘列表保存失败' . $e);
            return ResponeFails('保存失败');
        }
    }

    public function turntable_update_status(Request $request)
    {
        Validator::make($request->all(), [
            'pid'    => ['required', 'in:3,4'],
            'status' => ['required'],
        ], [
            'pid.required'    => '转盘类型必传',
            'pid.in'          => '转盘类型不在可选范围',
            'status.required' => '转盘状态不能为空'
        ])->validate();

        $res = ActivitiesNormal::query()->where('pid', $request->pid)
            ->update(['status' => $request->status]);
        if ($res) {
            return ResponeSuccess('更新成功');
        } else {
            return ResponeFails('更新失败');
        }
    }

    /*
     * 转盘奖励配置删除
     * */
    public function turntable_reward_delete(Request $request)
    {
        Validator::make($request->all(), [
            'pid'    => ['required', 'in:3,4'],
            'region' => ['required'],
        ], [
            'pid.required'    => '转盘类型必传',
            'pid.in'          => '转盘类型不在可选范围',
            'region.required' => '区域id必传',
        ])->validate();

        $res = RotaryConfig::query()->where(['pid' => $request->pid, 'region' => $request->region]);
        if (!$res->count()) {
            return ResponeFails('转盘奖励配置不存在');
        }
        $res = $res->delete();
        if ($res) {
            return ResponeSuccess('删除成功');
        }
        return ResponeFails('删除失败');
    }

    /*
    * 转盘功能设置列表
    *
    */
    public function turntable_effect_list(Request $request)
    {
        Validator::make($request->all(), [
            'pid' => ['required', 'in:3,4'],
        ], [
            'pid.required' => '转盘类型必传',
            'pid.in'       => '转盘类型不在可选范围',
        ])->validate();
        $list = ActivitiesNormal::query()->where('pid', $request->pid)->first();
        if ($list) {
            $content = json_decode($list->content, true);
            $content['score_lower_limit'] = realCoins($content['score_lower_limit']);
            $content['rank_condition'] = collect($content['rank_condition'])->transform(function ($item) {
                if ($item) $item = realCoins($item);
                return $item;
            });
            $list->btime = Carbon::parse($list->btime)->format('Y-m-d');
            $list->etime = Carbon::parse($list->etime)->format('Y-m-d');
            $list->content = $content;
        }

        return ResponeSuccess('请求成功', $list);
    }

    /*
     * 转盘功能设置
     * */
    public function turntable_effect_save(Request $request)
    {
        Validator::make($request->all(), [
            'start_time'           => ['required', 'date'],
            'end_time'             => ['required', 'date'],
            'big_win_notice_start' => ['required', 'numeric', 'min:1'],
            'big_win_notice_end'   => ['required', 'numeric', 'min:1'],
            'big_win_range_start'  => ['required', 'numeric', 'min:1'],
            'describe'             => ['required', 'max:225'],
            'pid'                  => ['required', 'in:3,4'],
        ], [
            'start_time.required'          => '开始时间必填',
            'end_time.required'            => '结束时间必填',
            'big_win_notice_start.numeric' => '大奖公告次数下限必须是数字',
            'big_win_notice_start.min'     => '大奖公告次数下限值必须大于0',
            'big_win_notice_end.numeric'   => '大奖公告次数上限必须是数字',
            'big_win_notice_end.min'       => '大奖公告次数上限值必须大于0',
            'big_win_range_start.numeric'  => '大奖范围下限必须是数字',
            'big_win_range_start.min'      => '大奖范围下限值必须大于0',
            'describe.required'            => '规则描述必填',
            'describe.max'                 => '规则描述不超过225个字符',
            'pid.required'                 => '转盘类型必传',
            'pid.in'                       => '转盘类型不在可选范围',
        ])->validate();

        $rank_switch_data = [];
        $rank_condition_data = [];
        foreach (ActivitiesNormal:: RANK_LIST as $key => $value) {
            $rank_switch = $request->{'switch_' . $key};
            $rank_condition = $request->{'condition_' . $key};
            $rank_switch_data += [
                $key => $rank_switch ? true : false,
            ];
            $rank_condition_data += [
                $key => $rank_condition ? moneyToCoins($rank_condition) : '',
            ];
            if ($rank_switch == true && !$rank_condition) {
                throw  new  NewException("{$value}开启条件未配置");
            }
        }
        try {
            $content = [
                'notice'            => [
                    'start' => $request->big_win_notice_start,
                    'end'   => $request->big_win_notice_end,
                ],
                'score_lower_limit' => moneyToCoins($request->big_win_range_start),
                'rank_switch'       => $rank_switch_data,
                'rank_condition'    => $rank_condition_data,
                'describe'          => $request->describe,
            ];
            ActivitiesNormal::query()->updateOrCreate(['pid' => $request->pid], [
                'content' => json_encode($content),
                'status'  => $request->status,
                'btime'   => $request->start_time . ' 00:00:00',
                'etime'   => $request->end_time . ' 23:59:59',
            ]);
            return ResponeSuccess('保存成功');
        } catch (\Exception $exception) {
            info('转盘配置保存失败' . $exception);
            return ResponeSuccess('保存失败');
        }
    }

    public function turntable_rank_list()
    {
        $list = [];
        foreach (ActivitiesNormal::RANK_LIST as $key => $vale) {
            $list[] = [
                'key'  => $key,
                'name' => $vale
            ];
        }
        return ResponeSuccess('请求成功', $list);
    }

    public function updateOrCreate($type, $request, $region = 0)
    {
        foreach (ActivitiesNormal::RANK_LIST as $key => $value) {
            $reward = $request->{'reward_' . $key};
            $weight = $request->{'weight_' . $key};
            $basic_query = RotaryConfig::query();
            if ($type == 'create') {
                (clone $basic_query)->create([
                    'pid'       => $request->pid,
                    'rank_type' => $key,
                    'reward'    => $reward ? moneyToCoins($reward) : 0,
                    'weight'    => $weight ?? 0,
                    'region'    => $region + 1,
                ]);
            } else {
                (clone $basic_query)->where(['pid' => $request->pid, 'region' => $request->region, 'rank_type' => $key])->update([
                    'reward' => $reward ? moneyToCoins($reward) : 0,
                    'weight' => $weight ?? 0,
                ]);
            }
        }
    }
}
