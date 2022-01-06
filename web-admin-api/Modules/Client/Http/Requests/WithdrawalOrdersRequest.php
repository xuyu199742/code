<?php

namespace Modules\Client\Http\Requests;

use App\Rules\UserExist;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalOrdersRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'user_id'=>'用户ID',
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
        ];
    }

    public function messages()
    {
        return [
            'user_id.required'=>'缺少参数',
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
