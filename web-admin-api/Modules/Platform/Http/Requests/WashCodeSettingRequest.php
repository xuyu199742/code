<?php

namespace Modules\Platform\Http\Requests;

use App\Rules\WashCodeUpperLimit;
use Illuminate\Foundation\Http\FormRequest;

class WashCodeSettingRequest extends FormRequest
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
            'platform_id' => 'required|integer',
            'jetton_score' => ['required', 'integer', 'min:2', new WashCodeUpperLimit($this->route('id'))],
            'vip_proportion' => 'required',
            'vip_proportion.*' => 'required|numeric',
        ];
    }

    public function messages()
    {
        $messages = [
            'category_id.integer' => '分类ID为整数',
            'category_id.required' => '分类ID不能为空',
            'platform_id.integer' => '平台ID为整数',
            'platform_id.required' => '平台ID不能为空',
            'jetton_score.required' => '有效投注区间上限不能为空',
            'jetton_score.integer' => '有效投注区间上限为整数',
            'jetton_score.min' => '有效投注区间上限最小值不能为1',
            'vip_proportion.required' => 'VIP比例不能为空',
        ];
        // validation vip proportion
        foreach ($this->post('vip_proportion') ?? [] as $key => $val) {
            $messages["vip_proportion.$key.required"] = "VIP" . substr($key, 3) . "比例不能为空";
            $messages["vip_proportion.$key.numeric"] = "VIP" . substr($key, 3) . "比例为数字";
        }

        return $messages;
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
