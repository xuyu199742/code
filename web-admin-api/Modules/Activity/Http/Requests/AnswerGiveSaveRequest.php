<?php

namespace Modules\Activity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerGiveSaveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'answer_min'        => ['required','integer','min:1'],
            'answer_max'        => ['required','integer','min:1',(request('answer_min') ? 'gte:answer_min' : '')],
            'show_min'          => ['required','integer','min:1'],
            'show_max'          => ['required','integer','min:1',(request('show_min') ? 'gte:show_min' : '')],
            'interval'          => ['required','integer','min:1'],
            'answer_num'        => ['required','integer','min:1'],
            'status'            => ['required','integer','in:0,1'],
        ];
    }

    public function messages()
    {
        return [
            'answer_min.required'      => '答题赠送下限不能为空',
            'answer_min.numeric'       => '答题赠送下限为数字',
            'answer_min.min'           => '答题赠送下限最小值为1',
            'answer_max.required'      => '答题赠送上限不能为空',
            'answer_max.numeric'       => '答题赠送上限为数字',
            'answer_max.min'           => '答题赠送上限最小值为1',
            'answer_max.gte'           => '答题赠送上限必须大于等于下限',
            'show_min.required'        => '显示赠送下限不能为空',
            'show_min.numeric'         => '显示赠送下限为数字',
            'show_min.min'             => '显示赠送下限最小值为1',
            'show_max.required'        => '显示赠送上限不能为空',
            'show_max.numeric'         => '显示赠送上限为数字',
            'show_max.min'             => '显示赠送上限最小值为1',
            'show_max.gte'             => '显示赠送上限必须大于等于下限',
            'interval.required'        => '间隔时间不能为空',
            'interval.integer'         => '间隔时间为整数',
            'interval.min'             => '间隔时间最小值为1',
            'answer_num.required'      => '答题次数不能为空',
            'answer_num.integer'       => '答题次数为整数',
            'answer_num.min'           => '答题次数最小值为1',
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
