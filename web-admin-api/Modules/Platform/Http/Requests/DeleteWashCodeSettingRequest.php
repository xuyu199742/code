<?php

namespace Modules\Platform\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteWashCodeSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => 'required',
            'category_id' => 'required|int',
            'platform_id' => 'required|int',
            'ids.*' => 'integer',
        ];
    }

    public function messages()
    {
        return [
            'ids.required' => '删除洗码ID不能为空',
            'category_id.integer' => '分类ID为整数',
            'platform_id.integer' => '平台ID为整数',
            'platform_id.required' => '平台ID不能为空',
            'category_id.required' => '分类ID不能为空',
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
