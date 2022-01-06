<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Models\Accounts\SystemStatusInfo;

class updateRegisterIpRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'register_info' => 'required',
            'register_info.*' => 'required|numeric|in:1,0',
        ];
    }

    public function messages()
    {
        $messages = [
            'register_info.required' => '注册配置信息不能为空',
        ];

        foreach ($this->post('register_info') ?? [] as $key => $val) {
            $messages["register_info.$key.required"] = SystemStatusInfo::REGISTER_INFO[$key] . "不能为空";
            $messages["register_info.$key.numeric"] = SystemStatusInfo::REGISTER_INFO[$key] . "为数字";
            $messages["register_info.$key.in"] = SystemStatusInfo::REGISTER_INFO[$key] . "值只能1和0";
        }

        return $messages;
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
