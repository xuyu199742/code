<?php

namespace Modules\Client\Http\Requests;

use App\Rules\UserExist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Models\Record\RecordTreasureSerial;

class AccountDetailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'nullable|integer',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'start_time.date' => '开始时间格式有误',
            'end_time.date' => '结束时间格式有误',
            'type.integer' => '流水类型为整数',
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
