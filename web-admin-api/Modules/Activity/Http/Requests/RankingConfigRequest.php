<?php

namespace Modules\Activity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RankingConfigRequest extends FormRequest
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
                'kind_id'           => 'required|integer',
                'server_id'         => 'required|integer',
                'type'              => 'required|in:1,2',
                'min_times'         => 'required|integer|min:1',
                'min_activity_num'  => 'required|integer|min:1',
                'min_award_num'     => 'required|integer|min:1',
                'start_time'        => 'required',
                'end_time'          => 'required',
                'awards'            => 'required|array',
            ];
        }
    }

    public function messages()
    {
        return [
            'kind_id.required'          => '游戏类型不能为空',
            'kind_id.integer'           => '游戏类型为整型',
            'server_id.required'        => '游戏房间类型为整型',
            'server_id.integer'         => '游戏房间类型为整型',
            'type.required'             => '类型不能为空',
            'type.in'                   => '类型不在可选范围内',
            'min_times.required'        => '最低局数不能为空',
            'min_times.integer'         => '最低局数为整型',
            'min_times.min'             => '最低局数最小值为1',
            'min_activity_num.required' => '最低活动人数不能为空',
            'min_activity_num.integer'  => '最低活动人数为整型',
            'min_activity_num.min'      => '最低活动人数最小值为1',
            'min_award_num.required'    => '中奖人数不能为空',
            'min_award_num.integer'     => '中奖人数人数为整型',
            'min_award_num.min'         => '中奖人数人数最小值为1',
            'start_time.required'       => '开始时间不能为空',
            'end_time.required'         => '结束时间不能为空',
            'awards.required'           => '奖励配置不能为空',
            'awards.array'              => '奖励配置必须是数组格式',
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
