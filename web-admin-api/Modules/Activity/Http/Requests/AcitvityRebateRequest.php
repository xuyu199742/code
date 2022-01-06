<?php

namespace Modules\Activity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcitvityRebateRequest extends FormRequest
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
                'name'              => 'required|max:32',
                'nullity'           => 'in:0,1',
                'category'          => 'in:1,2,3,4',
                'start_time'        => 'required|date',
                'end_time'          => 'required|date',
                'weal'              => 'required|array',
                //'image'             => ['image'],
                //'img_address'       => ['required'],
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required'         => '活动名称不能为空',
            'name.max'              => '活动名称最大不能超过32个字符',
            'nullity.in'            => '状态不在可选范围内',
            'category.in'           => '类型不在可选范围内',
            'start_time.required'   => '开始时间必填',
            'start_time.date'       => '开始时间非法',
            'end_time.required'     => '结束时间必填',
            'end_time.date'         => '结束时间非法',
            'weal.required'         => '福利配置不能为空',
            'weal.array'            => '福利配置必须是数组格式',
            //'image.image'           => '图片类型不正确',
            //'img_address.required'  => '图片必传',
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
