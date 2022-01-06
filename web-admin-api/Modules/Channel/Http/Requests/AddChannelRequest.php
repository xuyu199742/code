<?php

namespace Modules\Channel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Models\Agent\ChannelInfo;

class AddChannelRequest extends FormRequest
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
        $table = (new ChannelInfo())->getTable();
        $rules = $this->input('channel_id') ? [] : ['password' => 'required|min:6'];
        return array_merge([
            'nickname'        => 'max:50',
            'return_rate'     => 'required',
            'channel_domain'  => 'required',
            'phone'           => ['required', 'regex:/^1[345789][0-9]{9}$/', Rule::unique('agent.' . $table, 'phone')
                ->ignore($this->input('channel_id'),'channel_id')],
        ], $rules);
    }

    public function messages()
    {
        return [
            'password.required'       => '密码必须填写!',
            'password.min'            => '密码不少于6位!',
            'nickname.max'            => '昵称最多10个字符!',
            'phone.required'          => '手机号必须填写!',
            'return_rate.required'    => '返利比例必填!',
            'channel_domain.required' => '渠道推广域名必填!',
        ];

    }


}
