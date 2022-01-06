<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SigninSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'PackageID'     => 'required|numeric',
            'Probability'   => 'required|numeric|min:0|max:100'
        ];
    }

    public function messages()
    {
        return [
            'PackageID.required'    =>  '礼包不能为空',
            'PackageID.numeric'     =>  '礼包选择失败',
            'Probability.required'  =>  '礼包概率不能为空',
            'Probability.integer'   =>  '礼包概率必须是数字',
            'Probability.min'       =>  '礼包概率最小为0',
            'Probability.max'       =>  '礼包概率最大为100',
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
