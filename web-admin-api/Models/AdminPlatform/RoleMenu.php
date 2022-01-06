<?php

namespace Models\AdminPlatform;

use Illuminate\Support\Facades\DB;

class RoleMenu extends Base
{
	//数据表
	protected $table = 'role_menu';
    public    $timestamps = false;

    //绑定用户拥有的菜单
    public function bindMenu($role_id, $menu_ids)
    {
        foreach (array_unique($menu_ids) as $v){
            $data[] = ['role_id'=>$role_id,'menu_id'=>$v];
        }
        $db = DB::connection($this->connection);
        $db->beginTransaction();
        try {
            $this->where('role_id',$role_id)->delete();
            if (!$this->insert($data)){
                $db->rollBack();
                return false;
            }
            $db->commit();
            return true;
        }catch (\Exception $exception){
            $db->rollBack();
            return false;
        }
    }

}
