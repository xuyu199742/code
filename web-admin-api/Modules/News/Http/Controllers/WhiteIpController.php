<?php
/* IP白名单 */

namespace Modules\News\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\WhiteIp;
use Transformers\WhiteIpTransformer;
use Validator;

class WhiteIpController extends Controller
{
    /**
     * 白名单列表
     *
     * @return Response
     */
    public function white_ip_list()
    {
        $list = WhiteIp::paginate(config('page.list_rows'));
        return $this->response->paginator($list, new WhiteIpTransformer());
    }
   /*
    * 白名单保存
    *
    */
    public function  white_ip_save(Request $request)
    {
        Validator::make($request->all(), [
            'ip'          => ['required','regex:/^(?=(\b|\D))(((\d{1,2})|(1\d{1,2})|(2[0-4]\d)|(25[0-5]))\.){3}((\d{1,2})|(1\d{1,2})|(2[0-4]\d)|(25[0-5]))(?=(\b|\D))$/'],
        ], [
            'ip.required' => 'ip必填',
            'ip.regex'    => 'ip地址格式不正确',
        ])->validate();
        if ($request->input('id')) {
            $model = WhiteIp::find($request->input('id'));
            if (!$model) {
                return ResponeFails('ip地址不存在');
            }
        } else {
            $model = new WhiteIp();
        }
        $res=$model->where('ip',$request->input('ip'))->first();
        if($res){
            return ResponeFails('ip地址已存在');
        }
        $model -> ip       = $request->input('ip');
        $model -> nullity  = WhiteIp::NULLITY_ON;
        if ($model->save()) {
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }

    /*
     * 白名单删除
     * */
    public function white_ip_delete(Request $request)
    {
        $id = $request->input('id');
        if(!$id){
            return ResponeFails('id必传');
        }
        $model=WhiteIp::find($id);
        if(!$model){
            return ResponeFails('ip地址不存在');
        }
        $res =  $model->where('id', $id)->delete();
        if ($res) {
            return ResponeSuccess('删除成功');
        }
        return $this->response->errorInternal('删除失败');
    }
}
