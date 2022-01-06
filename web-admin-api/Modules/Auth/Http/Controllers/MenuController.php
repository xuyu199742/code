<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use libs\Tree;
use Models\AdminPlatform\Menu;
use Models\AdminPlatform\RoleMenu;

class MenuController extends Controller
{
    //获取页面权限菜单
    public function menuList()
    {
        $user_id = intval(request('user_id'));
        if (empty($user_id)){
            return ResponeFails('缺少参数或参数错误');
        }
        $Menu = new Menu();
        $list = $Menu->getMenuList($user_id);
        return ResponeSuccess('获取成功',$list);
    }

    //获取权限列表
    public function getMenuRule()
    {
        $role_id = intval(request('role_id'));
        if (empty($role_id)){
            return ResponeFails('缺少参数或参数错误');
        }
        $Menu = new Menu();
        $list['rule_ids'] = $Menu->getRoleMenu($role_id);
        $menus = $Menu->where('hidden',0)->orderBy('sort')->orderBy('id')->get()->toArray();
        $list['menu_list'] = Tree::toLayer($menus);
        return ResponeSuccess('获取成功',$list);
    }

    //绑定权限列表
    public function saveMenuRule(Request $request)
    {
        $role_id = intval(request('role_id'));
        $menu_ids = request('menu_ids');
        \Validator::make($request->all(), [
            "role_id"       => 'required|integer',
            "menu_ids"      => 'required|array',
            "menu_ids.*"    => 'required|integer|distinct',
        ])->validate();

        $RoleMenu = new RoleMenu();
        $res = $RoleMenu->bindMenu($role_id,$menu_ids);
        if (!$res){
            return ResponeFails('绑定失败');
        }
        return ResponeSuccess('绑定成功');
    }

}
