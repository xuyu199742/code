<?php

namespace Modules\Channel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckWithdrawRequest extends FormRequest
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
           // 'card_no'=>'max:18 |numeric',
            'card_no'   =>'required|regex:/^\d{16,18}$/',
            'bank_info' =>'required',
            'value'   =>'numeric|min:1',
            'phone'   =>'required |regex:/^1[345789][0-9]{9}$/',
        ];

    }
    public function messages()
    {
        return [
            'card_no.required'      =>'银行卡号必填!',
            'card_no.regex'         =>'银行卡号必须是16到18位数字!',
            'bank_info.required'    =>'开户银行必填!',
            'phone.required'        =>'手机号必须填写!',
            'phone.regex'           =>'手机号填写不合法!',
            'value.required'        =>'最少'.config('set.withdrawal').'1',
        ];

    }


}
