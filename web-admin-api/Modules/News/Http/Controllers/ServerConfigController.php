<?php
/* 服务器配置 */

namespace Modules\News\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\Accounts\SystemStatusInfo;
use Validator;

class ServerConfigController extends Controller
{
    /**
     * 系统维护配置展示
     *
     * @return Response
     */
    public function server_config_show()
    {
        $list = SystemStatusInfo::where('StatusName',SystemStatusInfo::STATUS_NAME)->first();
        return ResponeSuccess('请求成功', $list);
    }
   /*
    * 系统维护配置保存
    *
    */
    public function  server_config_save(Request $request)
    {
        Validator::make($request->all(), [
            'StatusValue'            => ['required','in:0,1'],
            'StatusString'           => ['required'],
        ], [
            'StatusValue.required'   => '状态数值必填',
            'StatusValue.in'         => '状态数值不在可选范围',
            'StatusString.required'  => '状态字符必填',
        ])->validate();
        $model = SystemStatusInfo::where('StatusName',SystemStatusInfo::STATUS_NAME)->first();
        if (!$model) {
            return ResponeFails('系统维护配置不存在');
        }
        $model -> StatusValue       = $request->input('StatusValue');
        $model -> StatusString      = $request->input('StatusString');
        if ($model->save()) {
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }

}
