<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RechargeWithdrawal extends FormRequest
{
    const ATTRIBUTES  =
        [
            'game_id'                   =>  '玩家ID',
            'channel_id'                =>  '渠道ID',
            'register_start_date'       =>  '注册日期结束时间',
            'register_end_date'         =>  '注册日期结束时间',
            'recharge_amount_min'       =>  '总充金额下限值',
            'recharge_amount_max'       =>  '总充金额上限值',
            'withdrawal_amount_min'     =>  '总提金额下限值',
            'withdrawal_amount_max'     =>  '总提金额上限值',
            'recharge_count_min'        =>  '总充次数下限值',
            'recharge_count_max'        =>  '总充次数上限值',
            'withdrawal_count_min'      =>  '总提次数下限值',
            'withdrawal_count_max'      =>  '总提次数上限值',
        ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'game_id'               =>  'nullable|regex:/^\d{0,9}(\,\d{0,9}){0,299}$/',
            'channel_id'            =>  'nullable|integer',
            'start_date'            =>  'nullable|date',
            'end_date'              =>  'nullable|date',
            'recharge_amount_min'   =>  'nullable|numeric|min:1',
            'recharge_amount_max'   =>  'nullable|numeric|min:1'.(request('recharge_amount_min') ? '|gte:recharge_amount_min' : ''),
            'recharge_count_min'    =>  'nullable|integer|min:1',
            'recharge_count_max'    =>  'nullable|integer|min:1'.(request('recharge_count_min') ? '|gte:recharge_count_min' : ''),
            'withdrawal_count_min'  =>  'nullable|integer|min:1',
            'withdrawal_count_max'  =>  'nullable|integer|min:1'.(request('withdrawal_count_min') ? '|gte:withdrawal_count_min' : ''),
            'withdrawal_amount_min' =>  'nullable|numeric|min:1',
            'withdrawal_amount_max' =>  'nullable|numeric|min:1'.(request('withdrawal_amount_min') ? '|gte:withdrawal_amount_min' : ''),
            'sort_field'            =>  'nullable|in:recharge_count,recharge_amount,withdrawal_count,withdrawal_amount,balance,change_score',
            'sort_order'            =>  'nullable|in:desc,asc',
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
            $rules[$key.'.regex']       = $request.'格式不正确';
        }
        return $rules;
    }
}
