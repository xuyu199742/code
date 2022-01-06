<?php

namespace Modules\Client\Http\Requests;

use App\Rules\UserExist;
use Illuminate\Foundation\Http\FormRequest;

class SendEmailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'UserID'    => ['required','numeric',new UserExist('none')],
            'Title'     => 'required|max:32',
            'Context'   => 'required|max:256',
        ];
    }

    public function messages()
    {
        return [
            'UserID.required'   =>  '用户标识不能为空',
            'UserID.numeric'    =>  '用户标识不不合法',
            'Title.required'    =>  '标题不能为空',
            'Title.max'         =>  '标题最大为32个字符',
            'Context.required'  =>  '内容不能为空',
            'Context.max'       =>  '内容最大为256个字符；',
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
