<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetRegisterIpRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'StatusValue' => 'required|numeric|max:999999999',
            'MachineCount' => 'required|numeric|min:0'
        ];
    }

    public function messages()
    {
        return [
            'StatusValue.required' => '同IP注册数不能为空',
            'StatusValue.numeric' => '同IP注册数必须是数字',
            'StatusValue.max' => '同IP注册数最大值为999999999',
            'MachineCount.required' => '同设备号注册不能为空',
            'MachineCount.numeric' => '同设备号注册必须是整数',
            'MachineCount.min' => '同设备号注册不能小于0',
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
