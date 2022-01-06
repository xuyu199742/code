<?php
/*短信配置---增、删、改操作都要更新缓存*/
namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Models\AdminPlatform\SystemSmsConfig;

class SmsConfigController extends Controller
{
    /**
     * 获取短信配置列表
     *
     */
    public function getList()
    {
        return SystemSmsConfig::all();
    }

    /**
     * 添加短信配置
     *
     */
    public function add()
    {
        $data = request()->all();
        $res = (new SystemSmsConfig)->saveSmsConfig($data);
        if ($res){
            Cache::forever('system_sms_config',SystemSmsConfig::all());//永久缓存
            return ResponeSuccess('添加成功');
        }else{
            return $this->response->errorInternal('添加失败');
        }
    }

    /**
     * 修改短信配置
     *
     */
    public function edit()
    {
        $data = request()->all();
        $res = (new SystemSmsConfig)->saveSmsConfig($data);
        if ($res){
            Cache::forever('system_sms_config',SystemSmsConfig::all());//永久缓存
            return ResponeSuccess('缓存成功');
        }else{
            return $this->response->errorInternal('修改失败');
        }
    }

    /**
     * 删除短信配置
     *
     */
    public function del()
    {
        $id = request()->input('id');
        $res = (new SystemSmsConfig())->where('id',$id)->delete();
        if ($res){
            Cache::forever('system_sms_config',SystemSmsConfig::all());//永久缓存
            return ResponeSuccess('删除成功');
        }else{
            return $this->response->errorInternal('删除失败');
        }
    }

    /**
     * 手动生成配置缓存文件
     *
     */
    public function cache()
    {
        $res = Cache::forever('system_sms_config',SystemSmsConfig::all());//永久缓存
        if ($res){
            return ResponeSuccess('缓存成功');
        }else{
            return $this->response->errorInternal('修改失败');
        }
    }

}
