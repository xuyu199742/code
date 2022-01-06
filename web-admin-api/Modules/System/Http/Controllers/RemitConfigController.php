<?php
namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\AdminPlatform\RemitConfig;
class RemitConfigController extends Controller
{
    public function getList()
    {
        $list = RemitConfig::get();
        return ResponeSuccess('获取成功',$list);
    }

    public function edit($id)
    {
        if (request()->isMethod('post')){
            \Validator::make(request()->all(), [
                'name'          => 'required|max:50',
                'mch_id'        => 'required|max:50',
                'mch_key'       => 'required|max:255',
                'status'        => 'in:0,1',
                'gateway'       => 'required|max:255',
                'min_money'     => 'required|integer|min:1',
                'max_money'     => 'required|integer|min:1',
            ], [
                'name.required'         => '代付名不能为空',
                'name.max'              => '代付名最大长度50个字符',
                'mch_id.required'       => '商户号不能为空',
                'mch_id.max'            => '商户号最大长度50个字符',
                'mch_key.required'      => '商户秘钥不能为空',
                'mch_key.max'           => '商户秘钥最大长度255个字符',
                'status.in'             => '状态不在范围内',
                'gateway.required'      => '网关不能为空',
                'gateway.max'           => '网关最大长度255个字符',
                'min_money.required'    => '最小金额不能为空',
                'min_money.integer'     => '最小金额为整数',
                'min_money.min'         => '最小金额不能小于1',
                'max_money.required'    => '最大金额不能为空',
                'max_money.integer'     => '最大金额为整数',
                'max_money.min'         => '最大金额不能小于1',
            ])->validate();

            $RemitConfig = RemitConfig::where('id',$id)->first();
            $RemitConfig->name = request('name');
            $RemitConfig->mch_id = request('mch_id');
            $RemitConfig->mch_key = request('mch_key');
            $RemitConfig->status = request('status');
            $RemitConfig->gateway = request('gateway');
            $RemitConfig->min_money = request('min_money');
            $RemitConfig->max_money = request('max_money');

            if (!$RemitConfig->save()){
                return ResponeFails('保存失败');
            }
            return ResponeSuccess('保存成功');
        }

    }
}
