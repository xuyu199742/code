<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SigninPackageGoodsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'PackageID'     => 'required|numeric',
            'GoodsNum'      => 'required|numeric|min:0.1|max:10',
        ];
    }

    public function messages()
    {
        return [
            'PackageID.required'    =>  '礼包不能为空',
            'PackageID.numeric'     =>  '礼包选择失败',
            'GoodsNum.required'     =>  '金币数量不能为空',
            'GoodsNum.integer'      =>  '金币数量必须是数字',
            'GoodsNum.min'          =>  '金币数量最小为0.1',
            'GoodsNum.max'          =>  '金币数量最大为10',
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
