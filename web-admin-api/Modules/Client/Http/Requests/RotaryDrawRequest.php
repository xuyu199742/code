<?php

namespace Modules\Client\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Models\Activity\ActivitiesNormal;

class RotaryDrawRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|integer',
            'rotary_type' => 'required|in:'.ActivitiesNormal::BETTING_TURNTABLE.','.ActivitiesNormal::RECHARGE_TURNTABLE,
            'rank_type' => 'nullable|in:'.implode(',',array_keys(ActivitiesNormal::RANK_LIST)),
        ];
    }

}
