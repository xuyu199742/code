<?php

namespace Modules\Platform\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetWashCodeSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => 'required|integer',
            'platform_id' => 'required|integer',
            'page' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'category_id.integer' => '分类ID为整数',
            'platform_id.integer' => '平台ID为整数',
            'platform_id.required' => '平台ID不能为空',
            'category_id.required' => '分类ID不能为空',
            'page.integer' => '分页页数为整数',
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
