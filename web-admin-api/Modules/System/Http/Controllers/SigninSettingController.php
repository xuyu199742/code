<?php
/*签到配置*/
namespace Modules\System\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\Platform\GameSignIn;
use Modules\System\Http\Requests\SigninSettingRequest;
use Transformers\GameSignInTransformer;

class SigninSettingController extends Controller
{
    /**
     * 签到配置列表
     *
     */
    public function getList()
    {
        $list = GameSignIn::orderBy('SortID','asc')->get();
        return $this->response->collection($list,new GameSignInTransformer());
    }

    /**
     * 签到配置添加
     *
     */
    public function add(SigninSettingRequest $request)
    {
        $res = GameSignIn::saveOne();
        if (!$res){
            return ResponeFails('添加失败');
        }
        signinInform();
        return ResponeSuccess('添加成功');
    }

    /**
     * 签到配置修改
     *
     */
    public function edit(SigninSettingRequest $request, $sign_id)
    {
        $res = GameSignIn::saveOne($sign_id);
        if (!$res){
            return ResponeFails('修改失败');
        }
        signinInform();
        return ResponeSuccess('修改成功');
    }

    /**
     * 签到配置删除
     *
     */
    public function del($sign_id)
    {
        $res = GameSignIn::where('SignID',$sign_id)->delete();
        if (!$res){
            return ResponeFails('删除失败');
        }
        signinInform();
        return ResponeSuccess('删除成功');
    }

}
