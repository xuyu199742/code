<?php

namespace Modules\Agent\Http\Requests;

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
        $rules = $this->input('channel_id')?[]:['password' => 'required|min:6'];
        return  array_merge([
            //'channel_id'      =>'required',
            'nickname'        =>'required|max:50',
            //'return_type'     =>'required',
            //'return_rate'     =>['required','numeric','min:1','integer'],
            'channel_domain'  => ['required', 'regex:/([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}/'],
            'phone'           => ['required', 'regex:/^1[345789][0-9]{9}$/',Rule::unique('agent.' . $table, 'phone')
                ->ignore($this->input('channel_id'),'channel_id')],
            'contact_address' =>'required',
            'reg_give'        =>'nullable|in:0,1',
            'bind_give'       =>'nullable|in:0,1',
        ],$rules);
    }
    public function messages()
    {
        return [
            'channel_id.required'       =>'没有渠道id',
            'nickname.required'       =>'姓名必须填写!',
            'nickname.max'            =>'昵称最多20个字符!',
            'password.required'       =>'密码必须填写!',
            'password.min'            =>'密码不少于6位!',
            'phone.required'          =>'手机号必须填写!',
            //'return_type.required'    =>'返利类型必填!',
            //'return_rate.required'    =>'返利比例必填!',
            //'return_rate.min'         =>'返利比例必须大于0!',
            //'return_rate.numeric'     =>'返利比例必须是数字!',
            //'return_rate.integer'     =>'返利比例必须是整数!',
            'channel_domain.required' =>'渠道推广域名必填!',
            'channel_domain.regex' => '域名不符合规范!',
            'contact_address.required'=>'联系地址必填!',
            'reg_give.in'           =>'注册赠送不在范围内!',
            'bind_give.in'          =>'绑定赠送不在范围内!',
        ];

    }


}
