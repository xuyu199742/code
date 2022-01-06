<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Models\Accounts\SystemStatusInfo;
use Modules\System\Http\Requests\SystemStatusInfoRequest;
use Validator;
class SystemStatusInfoController extends Controller
{
    //根据键名查看
    public function show($key)
    {
        try{
            $data = SystemStatusInfo::find($key);
        }catch (\Exception $exception){
            return ResponeFails('请求失败');
        }
        return ResponeSuccess('请求成功',$data);
    }

    //根据键名编辑
    public function edit($key,Request $request)
    {
        if (\request()->isMethod('get')){
            try{
                $data = SystemStatusInfo::find($key);
            }catch (\Exception $exception){
                return ResponeFails('请求失败');
            }
            return ResponeSuccess('请求成功',$data);
        }elseif (\request()->isMethod('put')){
            $validator = self::validator($key,$request->all());
            if($validator){
                return ResponeFails($validator);
            }
            $res = SystemStatusInfo::saveOne($key);
            if (!$res){
                return ResponeFails('修改失败');
            }
            return ResponeSuccess('修改成功');
        }
    }
    /**
     * 验证
     */
    private function validator($key,$info){
        switch ($key){
            case 'RevenueRateTake':
                Validator::make($info, [
                    'StatusValue'       => ['required','integer','min:0','max:100']
                ], [
                    'StatusValue.required'    =>  '取款税率不能为空',
                    'StatusValue.integer'     =>  '取款税率必须是整数',
                    'StatusValue.min'         =>  '取款税率最小值为0',
                    'StatusValue.max'         =>  '取款税率最大值为100',
                ])->validate();
                break;
            case 'AgentSettlementWay':
                Validator::make($info, [
                    'StatusValue'       => ['required','integer','in:1,2']
                ], [
                    'StatusValue.required'    =>  '代理结算方式必选',
                    'StatusValue.integer'     =>  '代理结算方式必须是整数',
                    'StatusValue.in'          =>  '代理结算方式不在可选范围内',
                ])->validate();
                break;
        }
        return false;
    }
    //修改体验积分
    public function experiencePoints($key,Request $request)
    {
        if (\request()->isMethod('get')){
            try{
                $data = SystemStatusInfo::find('ExperienceScore');
                $data['StatusName'] = 'ExperienceScore';
                $data['StatusValue'] = ($data['StatusValue'] ?? 0) / 10000;
            }catch (\Exception $exception){
                return ResponeFails('请求失败');
            }
            return ResponeSuccess('请求成功',$data);
        }elseif (\request()->isMethod('put')){
            Validator::make($request->all(), [
                'StatusValue'       => ['required','integer','min:0']
            ], [
                'StatusValue.required'    =>  '体验积分不能为空',
                'StatusValue.integer'     =>  '体验积分必须是整数',
                'StatusValue.min'         =>  '体验积分最小值为0',
            ])->validate();
            $res = SystemStatusInfo::saveOne('ExperienceScore');
            if (!$res){
                return ResponeFails('修改失败');
            }
            return ResponeSuccess('修改成功');
        }
    }

    //修改体验时长
    public function experienceTime($key,Request $request)
    {
        if (\request()->isMethod('get')){
            try{
                $data = SystemStatusInfo::find('ExperienceTime');
                $data['StatusName'] = 'ExperienceTime';
            }catch (\Exception $exception){
                return ResponeFails('请求失败');
            }
            return ResponeSuccess('请求成功',$data);
        }elseif (\request()->isMethod('put')){
            Validator::make($request->all(), [
                'StatusValue'       => ['required','integer','min:0']
            ], [
                'StatusValue.required'    =>  '体验时长不能为空',
                'StatusValue.integer'     =>  '体验时长必须是整数',
                'StatusValue.min'         =>  '体验时长最小值为0',
                'StatusValue.max'         =>  '体验时长最大值为100',
            ])->validate();
            $res = SystemStatusInfo::saveOne('ExperienceTime');
            if (!$res){
                return ResponeFails('修改失败');
            }
            return ResponeSuccess('修改成功');
        }
    }

}
