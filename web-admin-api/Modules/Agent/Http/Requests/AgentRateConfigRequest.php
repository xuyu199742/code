<?php

namespace Modules\Agent\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentRateConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'          => 'required',
            'water_min'     => 'required|integer|min:1|max:9999999999',
            //'water_max'     => 'required',
            'rebate'        => 'required|integer|min:1|max:9999999',
            'category_id'   => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'name.required'                 => '级别名称不能为空',
            'water_min.required'            => '区间下限不能为空',
            'water_min.integer'             => '区间下限应为整数',
            'water_min.min'                 => '区间下限最小为1',
            'water_min.max'                 => '区间下限最大为9999999999',
            'rebate.required'               => '提成比例不能为空',
            'rebate.integer'                => '提成比例应为整数',
            'rebate.min'                    => '提成比例最小为1',
            'rebate.max'                    => '提成比例最大为9999999',
            'category_id.integer'           => '游戏分类id应为整数',
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
