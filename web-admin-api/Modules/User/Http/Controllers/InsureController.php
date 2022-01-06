<?php
/*用户银行（游戏内的保险柜）存取*/
namespace Modules\User\Http\Controllers;
use Models\Treasure\RecordInsure;
use Transformers\RecordInsureTransformer;
class InsureController extends BaseController
{
    /**
     * 获取用户银行存取记录
     *
     */
    public function getList()
    {
        $user_id = intval(request('user_id'));
        $list = RecordInsure::where('SourceUserID',$user_id)->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new RecordInsureTransformer());
    }
}
