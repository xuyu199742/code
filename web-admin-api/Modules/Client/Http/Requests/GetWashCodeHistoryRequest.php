<?php

namespace Modules\Client\Http\Requests;

use App\Rules\UserExist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Models\Record\RecordTreasureSerial;

class GetWashCodeHistoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => '分类ID不能为空',
            'category_id.integer' => '分类ID为整数',
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
