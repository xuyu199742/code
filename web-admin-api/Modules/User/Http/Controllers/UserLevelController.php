<?php

namespace Modules\User\Http\Controllers;

use Models\Accounts\UserLevel;
use Modules\User\Http\Requests\UpdateUserLevelRequest;

class UserLevelController extends BaseController
{
    // 用户层级列表
    public function list()
    {
        $list = UserLevel::query()->paginate(20);
        return ResponeSuccess('获取成功', [$list]);
    }

    //用户层级更新
    public function update(UpdateUserLevelRequest $request, $id)
    {
        $userLevel = UserLevel::query()->where('ID', $id)->first();

        if (!$userLevel) {
            return ResponeSuccess('修改失败');
        }

        $userLevel->update([
            'bank' => $request->Bank,
            'Proxy' => $request->Proxy,
            'Withdrawal' => $request->Withdrawal,
            'PlayGame' => $request->PlayGame,
            'FlowRebate' => $request->FlowRebate,
            'BettingRebate' => $request->BettingRebate,
            'ProfitRebate' => $request->ProfitRebate,
            'LossRebate' => $request->LossRebate,
            'FreeBonus' => $request->FreeBonus,
            'RedpacketRain' => $request->RedpacketRain,
            'MammonActivity' => $request->MammonActivity,
            'LuckyTurntable' => $request->LuckyTurntable,
            'remark' => $request->remark,
        ]);

        return ResponeSuccess('修改成功');
    }
}
