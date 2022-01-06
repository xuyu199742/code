<?php

namespace Models\AdminPlatform;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class OrderLog extends Base
{
    public function admin()
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }

    public static function addLogs($content, $order_no, $order_type = '充值订单')
    {
        try {
            $ip = Request::getClientIp();
        } catch (\Exception $e) {
            $ip = '0.0.0.0';
        }
        try {
            $model             = new self();
            $model->admin_id   = Auth::guard('admin')->id() ?? 0;
            $model->log_info   = $content;
            $model->log_url    = Request::getRequestUri();
            $model->order_no   = $order_no;
            $model->order_type = $order_type;
            $model->log_ip     = $ip;
            if ($model->save()) {
                return true;
            }
        } catch (\Exception $e) {
            return true;
        }

    }
}
