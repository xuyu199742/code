<?php

namespace Modules\Platform\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OuterPlatformGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (request()->isMethod('get')){
            return [];
        }else{
            return [
                //'icon'          => 'required',
                //'icons'         => 'required',
                'name'          => 'required|max:50',
                'description'   => 'max:2000',
                'sort'          => 'integer|min:1',
                'status'        => 'in:1,2',
                'server_status' => 'in:1,2',
            ];
        }
    }

    public function messages()
    {
        return [
            //'icon.required'         => '游戏默认图标不能为空',
            //'icons.required'        => '游戏多个图标不能为空',
            'name.required'         => '游戏名称不能为空',
            'name.max'              => '游戏名称最大不能超过50个字符',
            'description.max'       => '简介最大为2000个字符',
            'sort.integer'          => '排序为整数',
            'sort.min'              => '排序最小为1',
            'status.in'             => '游戏状态值不在范围内',
            'server_status.in'      => '游戏维护状态值不在范围内',
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
