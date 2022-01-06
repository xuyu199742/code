<?php
/*系统配置*/

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Models\Accounts\SystemStatusInfo;
use Models\AdminPlatform\SystemSetting;
use Modules\System\Http\Requests\SetRegisterIpRequest;
use Modules\System\Http\Requests\updateRegisterIpRequest;
use mysql_xdevapi\Exception;
use Validator;

class SystemSettingController extends Controller
{
    private $tab = ['prots', 'coin', 'firstrecharge','remit','ali_remit','vip','withdraw_bet','recharge_percentage'];

    /**
     * 获取系统配置列表
     *
     */
    public function getList(Request $request)
    {
        Validator::make($request->all(), [
            'group' => ['required', Rule::in($this->tab)],
        ], [
            'group.required' => '分组名不能为空',
            'group.in'       => '分组名不在可选范围',
        ])->validate();
        $group = request('group');
        $list  = SystemSetting::where('group', $group)->get();
        if (count($list) > 0) {
            return ResponeSuccess('获取成功', $list);
        }
        $configs = config($group);
        $list    = [];
        $locked  = $configs['locked'] ?? [];
        unset($configs['locked']);
        foreach ($configs as $key => $value) {
            array_push($list, [
                'group' => $group,
                'key'   => $key,
                'value' => $value,
                'lock'  => in_array($key, $locked) ? SystemSetting::LOCKED : SystemSetting::UNLOCKED
            ]);
        }
        return ResponeSuccess('获取成功', $list);
    }

    /**
     * 修改系统配置
     *
     */
    public function edit(Request $request)
    {
        Validator::make($request->all(), [
            'group' => ['required', Rule::in($this->tab)],
            'info'  => ['required'],
        ], [
            'group.required' => '分组名不能为空',
            'group.in'       => '分组名不在可选范围',
            'info.required'  => '没有表单信息',
        ])->validate();
        $validator = self::validator(request('group'), request('info'));
        if($validator){
            return ResponeFails($validator);
        }
        $SystemSetting = new SystemSetting();
        $res           = $SystemSetting->edit(request('group'), request('info'));
        if ($res) {
            //Cache::forever('system_setting',SystemSetting::all());//永久缓存
            Cache::forget(request('group'));
            return ResponeSuccess('修改成功');
        } else {
            return ResponeFails('修改失败');
        }
    }

    /**
     * 验证
     */
    private function validator($group,$info){
        switch ($group){
            case 'recharge_percentage':
                foreach ($info as $k => $v) {
                    if($v['key'] == 'inner_recharge' && $v['value'] < 100){
                        return '内部充值赠送百分比不能小于100';
                    }
                    if($v['key'] == 'outside_recharge' && $v['value'] < 100){
                        return '外部充值赠送百分比不能小于100';
                    }
                    if($v['key'] == 'inner_recharge' && $v['value'] > 1000){
                        return '内部充值赠送百分比不能大于1000';
                    }
                    if($v['key'] == 'outside_recharge' && $v['value'] > 1000){
                        return '外部充值赠送百分比不能大于1000';
                    }
                }
                break;
            case 'withdraw_bet':
                foreach ($info as $k => $v) {
                    if($v['key'] == 'bet_multiple' && $v['value'] < 1){
                        return '打码量倍数不能小于1';
                    }
                }
                break;
        }
        return false;
    }


    /**
     * 手动生成配置缓存文件
     *
     */
    public function cache()
    {
        $res = Cache::forever('system_setting', SystemSetting::all());//永久缓存
        if ($res) {
            return ResponeSuccess('缓存成功');
        } else {
            return ResponeFails('修改失败');
        }
    }

    //注册ip设置
    public function updateRegisterIp(SetRegisterIpRequest $request)
    {
        $ip_res = SystemStatusInfo::query()->where('StatusName', 'LimitRegisterIPCount')
            ->update(['StatusValue' => $request->StatusValue]);
        $mer_res = SystemStatusInfo::query()->where('StatusName', 'LimitRegisterMachineCount')
            ->update(['StatusValue' => $request->MachineCount]);

        if(!$ip_res && $mer_res) {
            return ResponeFails('更新失败');
        }
        return ResponeSuccess('更新成功');
    }

    //注册信息设置
    public function updateRegisterInfo(updateRegisterIpRequest $request)
    {
        foreach ($request->register_info ?? [] as $key => $value) {
            SystemStatusInfo::query()->where('StatusName', $key)->update(['StatusValue' => $value]);
        }
        return ResponeSuccess('更新成功');
    }

    public function getRegisterInfo()
    {
        $info = SystemStatusInfo::query()->whereIn('StatusName', array_keys(SystemStatusInfo::REGISTER_INFO))->pluck('StatusValue', 'StatusName');
        $ip_count = SystemStatusInfo::query()->where('StatusName', 'LimitRegisterIPCount')->value('StatusValue');
        $mer_count = SystemStatusInfo::query()->where('StatusName', 'LimitRegisterMachineCount')->value('StatusValue');
        return ResponeSuccess('获取成功', ['info' => $info, 'ip_count'=> $ip_count, 'mer_count' => $mer_count]);
    }
}
