<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\RecordScoreDaily;

class BaseController extends Controller
{
    //后台管理员id
    protected function admin_id(){
        return Auth::guard('admin')->id();
    }

    /**
     * 获取用户绑定手机人数统计
     *
     * @param   array   $user_ids   用户id的集合
     *
     */
    protected function getUserBindCount($user_ids)
    {
        $arr_ids = AccountsInfo::where('RegisterMobile','<>','')->pluck('UserID')->toArray();
        return count(array_intersect($arr_ids,$user_ids));
    }

}
