<?php
/*用户充值*/
namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\PaymentOrder;
use Transformers\PaymentOrderTransformer;

class PayController extends BaseController
{
    /**
     * 获取用户充值记录
     *
     */
    public function getList()
    {
        $user_id = intval(request('user_id'));
        $list = PaymentOrder::where('user_id',$user_id)->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new PaymentOrderTransformer());
    }
}
