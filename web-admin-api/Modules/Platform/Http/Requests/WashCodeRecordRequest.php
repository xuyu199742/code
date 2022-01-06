<?php

namespace Modules\Platform\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WashCodeRecordRequest extends FormRequest
{
    public $list_rote = 'wash_code_record.list';
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(request()->route()->getName() == $this->list_rote){
            return [
                'game_id' => 'nullable|integer',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ];
        }
        return [
            'record_id' => 'required|integer',
            'category_id'=> 'required|integer'
        ];
    }

    public function messages()
    {
        if(request()->route()->getName() == $this->list_rote){
            return [
                'game_id.integer' => '玩家ID为整数',
                'start_date.date' => '开始时间格式有误',
                'end_date.date' => '结束时间格式有误',
            ];
        }
        return [
            'record_id.required' => '记录ID为必传',
            'record_id.integer' => '记录ID为整数',
            'category_id.required'=> '分类ID必传',
            'category_id.integer'=> '分类ID为整数',
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
