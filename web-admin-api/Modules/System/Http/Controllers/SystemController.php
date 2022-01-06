<?php
namespace Modules\System\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Accounts\SystemStatusInfo;

class SystemController extends Controller
{
    //禁止礼金设置
    public function forbidGifts(Request $request)
    {
        \Validator::make($request->all(), [
            'reg_give'          => 'in:0,1',
            'bind_give'         => 'in:0,1',
        ], [
            'reg_give.in'    => '注册赠送不在范围内',
            'bind_give.in'   => '绑定赠送不在范围内',
        ])->validate();
        if (request()->isMethod('post')){
            $str = request('bind_give',0) . request('reg_give',0);
            $StatusValue = base_convert($str,2,10);
            $res = SystemStatusInfo::where('StatusName','NoGiftMoney')->update(['StatusValue'=>$StatusValue]);
            if (!$res){
                return ResponeFails('修改失败');
            }
            return ResponeSuccess('修改成功');
        }
        $StatusValue = SystemStatusInfo::where('StatusName','NoGiftMoney')->value('StatusValue');
        $arr = SystemStatusInfo::FORBID_GIFTS;
        foreach ($arr as $k => $v){
            $arr[$k] = intval(($v & $StatusValue) > 0);
        }
        return ResponeSuccess('获取成功',$arr);
    }

}
