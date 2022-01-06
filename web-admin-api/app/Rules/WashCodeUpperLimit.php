<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Models\OuterPlatform\WashCodeSetting;

class WashCodeUpperLimit implements Rule
{
    private $message;
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {
        $value = $value * getGoldBase();
        $washCodes = WashCodeSetting::query()->Platform(request('platform_id'), request('category_id'))
            ->selectRaw('platform_id, category_id, upper_limit, MIN(id) as id')
            ->groupBy('platform_id', 'category_id', 'upper_limit')
            ->get();

        if (request()->isMethod('post')) {
            $upper_limit = $washCodes->max('upper_limit');
            if ($washCodes->count() >= 1) {
                if ($upper_limit >= $value) {
                    $this->message = '有效投注区间上限不能小于' . (int)realCoins($upper_limit) . '';
                    return false;
                }
            }
        } else {
            // when edit upper limit is not different.
            $wash_code = WashCodeSetting::query()->where('id', $this->id)->first();
            if ($value != $wash_code->upper_limit) {
                if ($washCodes->count() > 1 && $washCodes->max('upper_limit') != $wash_code->upper_limit) {
                    if ($value > $wash_code->upper_limit) {
                        $this->message = '有效投注区间上限不能大于' . (int)realCoins($wash_code->upper_limit) . '';
                        return false;
                    }
                    if ($value <= $wash_code->lower_limit) {
                        $this->message = '有效投注区间上限不能小于等于下限值' . (int)realCoins($wash_code->lower_limit) . '';
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}
