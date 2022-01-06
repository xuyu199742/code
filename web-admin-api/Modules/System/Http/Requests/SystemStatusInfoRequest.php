<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemStatusInfoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'StatusValue'       => 'required|numeric|min:0|max:99999999'
        ];
    }

    public function messages()
    {
        return [
            'StatusValue.required'    =>  '取款税率不能为空',
            'StatusValue.numeric'     =>  '取款税率必须是数字',
            'StatusValue.min'         =>  '取款税率最小值为1',
            'StatusValue.max'         =>  '取款税率最大值为100',
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
