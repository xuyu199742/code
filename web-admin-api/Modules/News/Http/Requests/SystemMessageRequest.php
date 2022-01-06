<?php

namespace Modules\News\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return  [
            'MessageString'    => 'required',
            'TimeRate'         => ['required','numeric','min:1'],
            'ServerRange'      => 'required',
            'StartTime'        => 'required|date_format:Y-m-d H:i:s',
            'ConcludeTime'     => 'required|date_format:Y-m-d H:i:s',
            'MessageType'      => 'in:1',
        ];

    }
    public function messages()
    {
        return [
            'MessageString.required'   => '消息内容必填',
            'ServerRange.required'     => '房间范围必填',
            'TimeRate.required'        => '消息频率必填',
            'TimeRate.numeric'         => '消息频率值必须是数字',
            'TimeRate.min'             => '消息频率值必须大于0',
            'StartTime.required'       => '开始时间必填',
            'ConcludeTime.required'    => '结束时间必填',
            'MessageType.in'           => '消息范围不在可选之中',
        ];

    }


}
