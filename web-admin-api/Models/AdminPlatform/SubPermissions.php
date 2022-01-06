<?php

namespace Models\AdminPlatform;

use Illuminate\Support\Facades\Auth;

class SubPermissions extends Base
{
	//数据表
	protected $table = 'sub_permissions';

	/**
	 * 判断是否具有权限
	 *
	 */
	public static function isRule($sign)
    {
        $admin = Auth::guard('admin')->user();
        if($admin->super() || self::where('admin_id',$admin->id)->where('sign',$sign)->first()){
            return true;
        }
        return false;
    }

}
