<?php

namespace Models\AdminPlatform;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
class LoginLog extends Base
{
    public function admin(){
        return $this->hasOne(AdminUser::class,'id','admin_id');
    }
    public static function firstLogin(){
        $id=Auth::guard('admin')->id();
        if(self::where('admin_id',$id)->exists()){
            return false;
        }
        return true;
    }
    public static function addLogs()
    {
        try {
            $ip = Request::getClientIp();
        } catch (\Exception $e) {
            $ip = '0.0.0.0';
        }
        try {
            $model             = new self();
            $model->admin_id   = Auth::guard('admin')->id() ?? 0;
            $model->log_url    = Request::getRequestUri();
            $model->log_ip     = $ip;
            if ($model->save()) {
                return true;
            }
        } catch (\Exception $e) {
            return true;
        }

    }
}
