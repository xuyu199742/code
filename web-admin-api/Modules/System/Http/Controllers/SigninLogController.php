<?php
/*签到记录*/
namespace Modules\System\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\Record\RecordGameSignIn;
use Transformers\RecordGameSignInTransformer;

class SigninLogController extends Controller
{
    /**
     * 签到记录列表
     *
     */
    public function getList()
    {
        $list = RecordGameSignIn::paginate(config('page.list_rows'));
        return $this->response->paginator($list,new RecordGameSignInTransformer());
    }

}
