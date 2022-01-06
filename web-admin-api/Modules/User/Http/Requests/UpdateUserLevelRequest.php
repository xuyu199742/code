<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLevelRequest extends FormRequest
{

    public function rules()
    {
        return [
            'Bank' => 'required|int|in:1,0',
            'Proxy' => 'required|int|in:1,0',
            'Withdrawal' => 'required|int|in:1,0',
            'PlayGame' => 'required|int|in:1,0',
            'FlowRebate' => 'required|int|in:1,0',
            'BettingRebate' => 'required|int|in:1,0',
            'ProfitRebate' => 'required|int|in:1,0',
            'LossRebate' => 'required|int|in:1,0',
            'FreeBonus' => 'required|int|in:1,0',
            'RedpacketRain' => 'required|int|in:1,0',
            'MammonActivity' => 'required|int|in:1,0',
            'LuckyTurntable' => 'required|int|in:1,0',
        ];
    }

    public function messages()
    {
        return [
            'Bank.required' => '使用银行不能为空',
            'Bank.int' => '使用银行为整数',
            'Bank.in' => '使用银行值只能1和0',

            'Proxy.required' => '允许代理不能为空',
            'Proxy.int' => '允许代理为整数',
            'Proxy.in' => '允许代理值只能1和0',

            'Withdrawal.required' => '允许'.config('set.withdrawal').'不能为空',
            'Withdrawal.int' => '允许'.config('set.withdrawal').'为整数',
            'Withdrawal.in' => '允许'.config('set.withdrawal').'值只能1和0',

            'PlayGame.required' => '允许游戏不能为空',
            'PlayGame.int' => '允许游戏为整数',
            'PlayGame.in' => '允许游戏值只能1和0',

            'FlowRebate.required' => '流水返利不能为空',
            'FlowRebate.int' => '流水返利为整数',
            'FlowRebate.in' => '流水返利值只能1和0',

            'BettingRebate.required' => '投注返利不能为空',
            'BettingRebate.int' => '投注返利为整数',
            'BettingRebate.in' => '投注返利值只能1和0',

            'ProfitRebate.required' => '盈利返利不能为空',
            'ProfitRebate.int' => '盈利返利为整数',
            'ProfitRebate.in' => '盈利返利值只能1和0',

            'LossRebate.required' => '损耗返利不能为空',
            'LossRebate.int' => '损耗返利为整数',
            'LossRebate.in' => '损耗返利值只能1和0',

            'FreeBonus.required' => '免费彩金不能为空',
            'FreeBonus.int' => '免费彩金为整数',
            'FreeBonus.in' => '免费彩金值只能1和0',

            'RedpacketRain.required' => '红包雨不能为空',
            'RedpacketRain.int' => '红包雨为整数',
            'RedpacketRain.in' => '红包雨值只能1和0',

            'MammonActivity.required' => '财神活动不能为空',
            'MammonActivity.int' => '财神活动为整数',
            'MammonActivity.in' => '财神活动值只能1和0',

            'LuckyTurntable.required' => '幸运转盘不能为空',
            'LuckyTurntable.int' => '幸运转盘为整数',
            'LuckyTurntable.in' => '幸运转盘值只能1和0',

        ];
    }

    public function authorize()
    {
        return true;
    }
}
