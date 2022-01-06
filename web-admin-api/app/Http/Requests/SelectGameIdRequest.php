<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectGameIdRequest extends FormRequest
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
        return [
            'game_id'  => ['nullable','regex:/^\d{0,9}(\,\d{0,9}){0,299}$/'],
        ];
    }

    public function messages()
    {
        return [
            'game_id.numeric' => '游戏id必须是数字，请重新输入！',
            'game_id.regex'   => '多个游戏id以英文逗号隔开',
        ];

    }
}
