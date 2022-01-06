<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FirstRechargeRequest extends FormRequest
{
    const ATTRIBUTES  =
        [
            'game_id'               =>  '玩家ID',
            'channel_id'            =>  '渠道ID',
            'first_start_date'      =>  '首充日期开始时间',
            'first_end_date'        =>  '首充日期结束时间',
            'register_start_date'   =>  '注册日期结束时间',
            'register_end_date'     =>  '注册日期结束时间',
            'first_min'             =>  '首充金额下限值',
            'first_max'             =>  '首充金额上限值',
            'recharge_min'          =>  '总充金额下限值',
            'recharge_max'          =>  '总充金额上限值',
            'withdrawal_min'        =>  '总提金额下限值',
            'withdrawal_max'        =>  '总提金额上限值',
        ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'game_id'               =>  'nullable|integer',
            'channel_id'            =>  'nullable|integer',
            'first_start_date'      =>  'nullable|date',
            'first_end_date'        =>  'nullable|date',
            'register_start_date'   =>  'nullable|date',
            'register_end_date'     =>  'nullable|date',
            'first_min'             =>  'nullable|numeric|min:1',
            'first_max'             =>  'nullable|numeric|min:1'.(request('first_min') ? '|gte:first_min' : ''),
            'recharge_min'          =>  'nullable|numeric|min:1',
            'recharge_max'          =>  'nullable|numeric|min:1'.(request('recharge_min') ? '|gte:recharge_min' : ''),
            'withdrawal_min'        =>  'nullable|numeric|min:1',
            'withdrawal_max'        =>  'nullable|numeric|min:1'.(request('withdrawal_min') ? '|gte:withdrawal_min' : ''),
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
        $rules = [];
        foreach(self::ATTRIBUTES as $key => $request){
            $rules[$key.'.integer']     = $request.'必须为整数';
            $rules[$key.'.numeric']     = $request.'必须为数字';
            $rules[$key.'.min']         = $request.'最小为1';
            $rules[$key.'.date']        = $request.'必须为时间格式';
            $rules[$key.'.gt']          = $request.'必须大于下限值';
        }
        return $rules;
    }
}
