<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JettonAnalysisRequest extends FormRequest
{
    const ATTRIBUTES  =
        [
            'game_id'               =>  '玩家ID',
            'channel_id'            =>  '渠道ID',
            'platform_id'           =>  '平台ID',
            'start_date'            =>  '开始时间',
            'end_date'              =>  '结束时间',
            'note_count_min'        =>  '注单数下限值',
            'note_count_max'        =>  '注单数上限值',
            'jetton_score_min'      =>  '有效投注下限值',
            'jetton_score_max'      =>  '有效投注上限值',
            'change_score_min'      =>  '输赢下限值',
            'change_score_max'      =>  '输赢上限值',
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
            'platform_id'           =>  'nullable|integer',
            'start_date'            =>  'nullable|date',
            'end_date'              =>  'nullable|date',
            'note_count_min'        =>  'nullable|integer|min:1',
            'note_count_max'        =>  'nullable|integer|min:1'.(request('note_count_min') ? '|gte:note_count_min' : ''),
            'jetton_score_min'      =>  'nullable|numeric|min:1',
            'jetton_score_max'      =>  'nullable|numeric|min:1'.(request('jetton_score_min') ? '|gte:jetton_score_min' : ''),
            'change_score_min'      =>  'nullable|numeric',
            'change_score_max'      =>  'nullable|numeric'.(request('change_score_min') ? '|gte:change_score_min' : ''),
            'sort_field'            =>  'nullable|in:note_count,jetton_score,change_score',
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
