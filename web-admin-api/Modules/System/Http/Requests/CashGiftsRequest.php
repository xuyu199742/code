<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashGiftsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'game_id'       => 'nullable|regex:/^\d{0,9}(\,\d{0,9}){0,299}$/',
            'channel_id'    => 'nullable|integer',
            'score_lower'   => 'nullable|numeric|min:1|max:999999999',
            'score_upper'   => 'nullable|numeric|min:1|max:999999999'.(request('score_lower') ? '|gte:score_lower' : ''),
            'total_lower'   => 'nullable|integer|min:1|max:999999999',
            'total_upper'   => 'nullable|integer|min:1|max:999999999'.(request('total_lower') ? '|gte:total_lower' : ''),
        ];
    }

    public function messages()
    {
        return [
            'start_date.date'    =>  '开始日期不合法',
            'end_date.date'      =>  '结束日期不合法',
            'game_id.integer'    =>  '玩家id必须是数字',
            'game_id.regex'      =>  '玩家id格式不正确',
            'channel_id.integer' =>  '渠道id必须是数字',
            'score_lower.min'    =>  '礼金领取'.config('set.amount').'下限最小为1',
            'score_lower.max'    =>  '礼金领取'.config('set.amount').'下限超出范围',
            'score_upper.min'    =>  '礼金领取'.config('set.amount').'上限最小为1',
            'score_upper.max'    =>  '礼金领取'.config('set.amount').'上限超出范围',
            'score_lower.gt'     =>  '礼金领取'.config('set.amount').'必须大于下限值',
            'total_lower.min'    =>  '礼金领取次数下限最小为1',
            'total_lower.max'    =>  '礼金领取次数下限超出范围',
            'total_upper.min'    =>  '礼金领取次数上限最小为1',
            'total_upper.max'    =>  '礼金领取次数上限超出范围',
            'total_lower.gt'     =>  '礼金领取次数必须大于下限值'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
