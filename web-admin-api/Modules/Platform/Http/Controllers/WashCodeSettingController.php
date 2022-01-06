<?php

namespace Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Matrix\Exception;
use Models\Accounts\MembersInfo;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\OuterPlatformGame;
use Models\OuterPlatform\WashCodeSetting;
use Models\OuterPlatform\WashCodeVip;
use Modules\Platform\Http\Requests\DeleteWashCodeSettingRequest;
use Modules\Platform\Http\Requests\GetWashCodeSettingRequest;
use Modules\Platform\Http\Requests\WashCodeSettingRequest;

class WashCodeSettingController extends Controller
{
    public function list(GetWashCodeSettingRequest $request)
    {
        $list = WashCodeSetting::query()->where('category_id', $request->category_id)
            ->AndFilterWhere('platform_id', $request->platform_id)
            ->selectRaw('platform_id, category_id, upper_limit, MIN(id) as id')
            ->with('vips')
            ->groupBy('platform_id', 'category_id', 'upper_limit')
            ->orderBy('upper_limit', 'asc')
            ->paginate(20)
            ->toArray();

        // 格式化返回数据
        $vip = MembersInfo::query()->select('id', 'MemberOrder')->pluck('MemberOrder');
        $list['data'] = $this->transformList($list['data'], $vip);

        return ResponeSuccess('获取成功', ['list' => $list, 'vip' => $vip,]);
    }

    public function create(WashCodeSettingRequest $request)
    {
        if (!OuterPlatform::query()->find($request->platform_id)) {
            return ResponeSuccess('无效的平台ID');
        }
        $vipSetting = [];
        $games = OuterPlatformGame::query()->where('platform_id', $request->platform_id)->pluck('kind_id');
        if(!$games->count() > 0 ) {
            return \Response::json(['data' => [], 'message' => '添加失败，当前平台下还没有配置游戏', 'status' => false], 200);
        }
        foreach ($games as $game) {
            $id = \DB::table(WashCodeSetting::tableName())->insertGetId([
                'upper_limit' => $request->jetton_score * getGoldBase(),
                'category_id' => $request->category_id,
                'kind_id' => $game,
                'platform_id' => $request->platform_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($request['vip_proportion'] ?? [] as $key => $item) {
                $vipSetting[] = [
                    'wash_code_setting_id' => $id,
                    'vip_proportion' => (string)$item,
                    'member_order' => substr($key, 3),
                ];
            }
        }

        foreach (array_chunk($vipSetting, 500) as $items) {
            \DB::table(WashCodeVip::tableName())->insert($items);
        }

        return ResponeSuccess('添加成功');
    }

    public function update(WashCodeSettingRequest $request, $id)
    {
//        $jetton_score = $request->jetton_score * getGoldBase();
//        $upper_limit = WashCodeSetting::query()->where('id', $id)->value('upper_limit');
//        $ids = WashCodeSetting::query()->Platform($request->platform_id, $request->category_id)->where('upper_limit', $upper_limit)->pluck('id');
//        if ($ids->count() < 1) {
//            return ResponeSuccess('该记录不存在');
//        }
//        \DB::table(WashCodeSetting::tableName())->whereIn('id', $ids)->update(['upper_limit' => $jetton_score]);
//        foreach ($request['vip_proportion'] ?? [] as $key => $item) {
//            WashCodeVip::query()->whereIn('wash_code_setting_id', $ids)
//                ->where('member_order', substr($key, 3))
//                ->update(['vip_proportion' => (string)$item,]);
//        }
//        return ResponeSuccess('修改成功');、
//        优化
//        1）针对VIP等级改为1-20导致的，其他VIP等级不能编辑保存的问题，先删除当前配置，再新增配置
//        2）针对平台底下游戏有变动，先删除，再重新配置
        try {
//            $jetton_score = $request->jetton_score * getGoldBase();
            $upper_limit = WashCodeSetting::query()->where('id', $id)->value('upper_limit');
            $ids = WashCodeSetting::query()->Platform($request->platform_id, $request->category_id)->where('upper_limit', $upper_limit)->pluck('id');
            \DB::beginTransaction(); //开启事务
            \DB::table(WashCodeVip::tableName())->whereIn('wash_code_setting_id', $ids)->delete();
            \DB::table(WashCodeSetting::tableName())->whereIn('id', $ids)->delete();

            $games = OuterPlatformGame::query()->where('platform_id', $request->platform_id)->pluck('kind_id');
            if(!$games->count() > 0 ) {
                return \Response::json(['data' => [], 'message' => '添加失败，当前平台下还没有配置游戏', 'status' => false], 200);
            }
            foreach ($games as $game) {
                $id = \DB::table(WashCodeSetting::tableName())->insertGetId([
                    'upper_limit' => $request->jetton_score * getGoldBase(),
                    'category_id' => $request->category_id,
                    'kind_id' => $game,
                    'platform_id' => $request->platform_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                foreach ($request['vip_proportion'] ?? [] as $key => $item) {
                    $vipSetting[] = [
                        'wash_code_setting_id' => $id,
                        'vip_proportion' => (string)$item,
                        'member_order' => substr($key, 3),
                    ];
                }
            }
            foreach (array_chunk($vipSetting, 500) as $items) {
                \DB::table(WashCodeVip::tableName())->insert($items);
            }
            \DB::commit();
            return ResponeSuccess('修改成功');
        } catch (Exception $e) {
            \DB::rollback();
            return ResponeSuccess('修改失败');
        }
    }

    public function delete(DeleteWashCodeSettingRequest $request)
    {
        $upper_limits = WashCodeSetting::query()->whereIn('id', $request->ids ?? [])->pluck('upper_limit');

        $res = WashCodeSetting::query()->Platform($request->platform_id, $request->category_id)
            ->whereIn('upper_limit', $upper_limits)
            ->get()
            ->map(function ($model) {
                $model->delete();
            });

        $message = $res->count() > 0 ? '删除成功' : '删除失败';

        return ResponeSuccess($message);
    }

    public function transformList($list, $vips)
    {
        $list_data = [];
        foreach ($list as $key => $item) {
            $list_data[$key] = [
                'number' => $key + 1 + request('number') ?? 0,
                'id' => $item['id'],
                'jetton_score_between' => (int)realCoins($item['lower_limit']) . '-' . (int)realCoins($item['upper_limit']),
                'jetton_score' => (int)realCoins($item['upper_limit']),
            ];
            foreach ($vips as $vip) {
                $list_data[$key]['VIP' . $vip] = (float)bcadd(collect(Arr::get($item, 'vips', []))->firstWhere('member_order', $vip)['vip_proportion'] ?? 0, 0, 2) . '%';
            }
        }
        return $list_data;
    }
}
