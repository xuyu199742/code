<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Models\AdminPlatform\AdminUser;

class AddAdminUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $table = (new AdminUser())->getTable();
        $id    = $this->input('id');
        return [
            'id'                    => 'numeric',
            'username'              => 'required',
            'email'                 => 'required|email|unique:' . $table . ',email,' . $id,
            'mobile'                => 'required|numeric|regex:/^1[345789][0-9]{9}$/|unique:' . $table . ',mobile,' . $id,
            'sex'                   => 'required|in:0,1',
            'password'              => 'required_without:id|min:8|confirmed',
            'password_confirmation' => 'required_without:id',
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
            'username.required'              => '用户名必填',
            'email.required'                 => '邮箱必填',
            'mobile.required'                => '手机号必填',
            'mobile.regex'                   => '手机号不合法',
            'sex.required'                   => '性别必选',
            'old_password.required_with'     => '旧密码必填',
            'password.required'              => '密码必填',
            'password_confirmation.required' => '确认密码必填',
            'email.unique'                   => '邮箱已经被使用',
            'mobile.unique'                  => '手机号已经被使用',
            'password.confirmed'             => '两次密码不一致',

        ];
    }
}
