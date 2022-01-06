<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * 用户金币赠送规则
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
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

    /**
     * 用户金币赠送规则验证对应的消息提示
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.required'                             => '新密码不能为空',
            'password.min'                                  => '新密码最少为6个字符',
            'password.confirmed'                            => '两次密码不一致',
            'password_confirmation.required'                => '确认密码不能为空',
        ];
    }
}
