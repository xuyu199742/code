<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserInformRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'Title'         => 'required',
            'Context'       => 'required',
            'SendType'      => 'required|in:0,1,2,3',
            'TimeType'      => 'required|in:1,2',
            'ChannelID'     => 'nullable|numeric',
            'StartTime'     => 'nullable|date_format:Y-m-d H:i:s|after:'.date('Y-m-d H:i:s'),
        ];
    }

    public function messages()
    {
        return [
            'Title.required'        => '标题不能为空',
            'Context.required'      => '内容不能为空',
            'SendType.required'     => '发送范围不能为空',
            'SendType.in'           => '发送范围不在区间内',
            'TimeType.required'     => '发送时间类型不能为空',
            'TimeType.in'           => '发送时间类型不在范围内',
            'ChannelID.numeric'     => '渠道id必须是数字',
            'StartTime.date_format' => '设置定时日期不合法',
            'StartTime.after'       => '设置定时日期不在范围内',
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
