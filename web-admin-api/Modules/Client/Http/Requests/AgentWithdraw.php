<?php

namespace Modules\Client\Http\Requests;

use App\Rules\UserExist;
use Illuminate\Foundation\Http\FormRequest;

class AgentWithdraw extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id'       =>['required', new UserExist()],
            'name'          =>'nullable',
            'score'         =>'nullable|numeric|min:1|max:100000',
            'phonenum'      =>'nullable',
            'back_name'     =>'nullable',
            'back_card'     =>'nullable',
        ];
    }

    public function attributes()
    {
        return [
            'user_id'       =>'用户ID',
            'name'          =>'姓名',
            'score'         => config('set.withdrawal').config('set.amount'),
            'phonenum'      =>'手机号',
            'back_name'     =>'开户行',
            'back_card'     => config('set.safe').'卡号',
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

    public function messages()
    {
        return [
            'score.required'    => config('set.withdrawal').config('set.amount').'不能为空',
            'score.integer'     => config('set.withdrawal').config('set.amount').'输入有误',
            'score.min'         =>'单笔最小'.config('set.withdrawal').config('set.amount').'为1'.config('set.rmb'),
            'score.max'         =>'单笔最大'.config('set.withdrawal').config('set.amount').'为100000'.config('set.rmb'),
        ];
    }
}
