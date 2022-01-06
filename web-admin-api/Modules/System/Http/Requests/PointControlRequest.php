<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PointControlRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'game_id'       => 'required|integer',
            'control_type'  => 'required|in:1,2',
            'number'        => 'required|numeric',
            'priority'      => 'required|in:1,2,3,4',
            'probability'   => 'nullable|numeric|min:0|max:100',
            'target'        => 'nullable|in:1,2'
        ];
    }

    public function messages()
    {
        return [
            'game_id.required'      => '目标id必填',
            'game_id.integer'       => '目标id必须是整数',
            'control_type.required' => '控制方式必选',
            'control_type.in'       => '控制方式不在可选范围内',
            'number.required'       => '目标金币或者目标局数必填',
            'number.numeric'        => '目标金币或者目标局数必须是数字',
            'priority.required'     => '优先级必选',
            'priority.in'           => '优先级不在可选范围内',
            'probability.integer'   => '获胜概率必须是数字',
            'probability.min'       => '获胜概率最小为0',
            'probability.max'       => '获胜概率最大为100',
            'target.in'             => '胜负目标不在可选范围内',
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
