<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BettingReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
//            'sort' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return  [
            'start_time.date' => '开始时间格式有误',
            'end_time.date' => '开始时间格式有误',
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
