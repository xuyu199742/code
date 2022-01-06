<?php
/*用户赠送金币验证*/
namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class GoldGiveRequest extends FormRequest
{
    /**
     * 用户金币赠送规则
     *
     * @return array
     */
    public function rules()
    {
        return [
            'game_id'   => 'required|integer',
            'add_gold'  => 'required|numeric|min:-999999|max:999999',
            'reason'    => 'required|max:200',
            'multiple'  => 'required|numeric',
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
            'game_id.required'              => '用户标识不能为空',
            'game_id.integer'               => '用户标识有误',
            'add_gold.required'             => '赠送金币不能为空',
            'add_gold.numeric'              => '赠送金币输入有误',
            'add_gold.min'                  => '赠送金币最小为-999999',
            'add_gold.max'                  => '赠送金币最大为999999',
            'reason.required'               => '赠送说明不能为空',
            'reason.max'                    => '赠送说明最大为200个字',
            'multiple.required'             => config('set.auditBet').'不能为空',
            'multiple.numeric'              => config('set.auditBet').'必须是一个数字',
        ];
    }
}
