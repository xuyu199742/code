<?php
/*签到礼包配置*/
namespace Modules\System\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\Platform\GamePackage;
use Transformers\GamePackageTransformer;

class SigninGiftSettingController extends Controller
{
    //签到礼包配置用于下拉框选择
    public function getAll()
    {
        $list = GamePackage::select('PackageID','Name')->orderBy('SortID','desc')->get();
        return ResponeSuccess('获取成功',$list);
    }

    //签到礼包配置列表
    public function getList()
    {
        $list = GamePackage::orderBy('SortID','desc')->get();
        return $this->response->collection($list,new GamePackageTransformer());
    }

    //签到礼包配置添加
    public function add()
    {
        $res = GamePackage::saveOne();
        if (!$res){
            return ResponeFails('添加失败');
        }
        signinInform();
        return ResponeSuccess('添加成功');
    }

    //签到礼包配置修改
    public function edit($package_id)
    {
        $res = GamePackage::saveOne($package_id);
        if (!$res){
            return ResponeFails('修改失败');
        }
        signinInform();
        return ResponeSuccess('修改成功');
    }

    //签到礼包配置删除
    public function del($package_id)
    {
        $res = GamePackage::where('PackageID',$package_id)->delete();
        if (!$res){
            return ResponeFails('删除失败');
        }
        signinInform();
        return ResponeSuccess('删除成功');
    }

}
