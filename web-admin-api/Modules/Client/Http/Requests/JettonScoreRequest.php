<?php

namespace Modules\Client\Http\Requests;

use App\Rules\UserExist;
use Illuminate\Foundation\Http\FormRequest;

class JettonScoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => 'nullable|integer',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'platform_id' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            // 'category_id.required' => '分类ID不能为空',
            'category_id.integer' => '分类ID为整数',
            'start_time.date' => '开始时间格式有误',
            'end_time.date' => '结束时间格式有误',
            'platform_id.integer' => '平台ID为整数',
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
