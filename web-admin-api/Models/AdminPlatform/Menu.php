<?php

namespace Models\AdminPlatform;

use Illuminate\Support\Facades\DB;
use libs\Tree;

class Menu extends Base
{
	//数据表
	protected $table = 'menu';
    public    $timestamps = false;

    //获取菜单
    public function getMenuList($user_id = null)
    {
        if (empty($user_id) || in_array($user_id, config('super.id'))){
            $list = $this->orderBy('sort')->orderBy('id')->get();
            return Tree::toLayer($list->toArray());
        }else{
            return Tree::toLayer($this->getUserMenu($user_id)->toArray());
        }
    }

    //获取用户拥有的菜单
    public function getUserMenu($user_id)
    {
        $role_arr = DB::table("model_has_roles")->where('model_id',$user_id)->pluck('role_id')->toArray();
        $menu_id_arr = RoleMenu::whereIn('role_id',$role_arr)->pluck('menu_id')->toArray();
        return $this->whereIn('id',$menu_id_arr)->orWhere('hidden',1)->orderBy('sort')->orderBy('id')->get();
    }

    //获取角色拥有的菜单集合
    public function getRoleMenu($role_id)
    {
        return RoleMenu::where('role_id',$role_id)->pluck('menu_id')->toArray();
    }

    //获取用户拥有的权限集合
    public function getUserRules($user_id)
    {
        $menu_ids = $this->getUserMenu($user_id)->pluck('id')->toArray();
        return DB::table("menu_apis")->whereIn('menu_id',$menu_ids)->pluck('name')->toArray();
    }

}
