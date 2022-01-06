<?php

namespace Modules\Client\Http\Requests;

use App\Rules\EnableWithdraw;
use App\Rules\UserExist;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'user_id'=>'用户ID',
            'channel_no'=>'渠道/代理号',
            'card_no'=>'银行卡号',
            'payee'=>'收款人',
            'phone'=>'手机号',
            'gold_coins'=> config('set.withdrawal').'金币',
            'bank_info'=>'银行信息',
        ];

    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id'=>['required', new UserExist()],
            'card_no'=>'required',
            'payee'=>'required',
            'phone'=>'required|numeric',
            'bank_info'=>'required',
            'gold_coins'=>['required','numeric',new EnableWithdraw($this->input('user_id'))],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required'=>'缺少参数',
            'card_no.required'=>'银行卡号必填',
            'payee.required'=>'收款人必填',
            'phone.required'=>'手机号必填',
            'gold_coins.numeric'=>'金币必须是数字且为10的倍数',
            'gold_coins.required'=> config('set.withdrawal').'金币必填',
            'bank_info.required'=>'银行信息必填',
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
