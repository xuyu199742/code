<?php

namespace Modules\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Models\AdminPlatform\Version;

class VersionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id=$this->input('id');
        $table=(new Version())->getTable();
        return [
            'version_id'          => ['required','numeric',Rule::unique($table)->ignore($id)],
            'version_description' => 'required|max:50',
            'hot_update_url'      => 'required|url',
            'force_update_url'    => 'required|url',
            'status'              => 'required|numeric|in:0,1',
            /*'recharge_percent'    => 'required|numeric',
            'recharge_status'     => 'required|numeric|in:0,1',
            'withdrawal_percent'  => 'required|numeric',
            'withdrawal_status'   => 'required|numeric|in:0,1',
            'min_withdrawal'      => 'required|numeric',
            'withdrawal_desc'     => 'required',*/
        ];
    }

    public function messages()
    {
        return [
            'version_id.required'          => '版本号必填',
            'version_id.numeric'           => '版本号必须是数字',
            'version_id.unique'            => '版本号已经存在',
            'version_description.required' => '版本名称不能为空',
            'version_description.max'      => '版本名称不能大于50个字符',
            'hot_update_url.required'      => '热更地址必填',
            'hot_update_url.url'           => '热更地址必须是完整的url',
            'force_update_url.required'    => '强更地址必填',
            'force_update_url.url'         => '强更地址必须是完整的url',
            'status.required'              => '版本开关不能为空',
            'status.numeric'               => '版本开关状态必须是数字',
            'status.in'                    => '版本开关值不正确',
            /*'recharge_percent.required'    => '充值比例不能为空',
            'recharge_percent.numeric'     => '充值比例必须是数字',
            'recharge_status.numeric'      => '充值开关状态必须是数字',
            'recharge_status.in'           => '充值开关值不正确',
            'withdrawal_percent.required'  => '比例不能为空',
            'withdrawal_percent.numeric'   => '比例必须是数字',
            'withdrawal_status.numeric'    => '开关状态必须是数字',
            'withdrawal_status.in'         => '开关值不正确',
            'min_withdrawal.required'      => '最低值不能为空',
            'min_withdrawal.numeric'       => '最低值必须是数字',
            'withdrawal_desc.required'     => '说明不能为空',*/
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
