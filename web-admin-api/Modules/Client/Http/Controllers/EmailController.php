<?php

namespace Modules\Client\Http\Controllers;

use App\Jobs\MsgPush;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Models\Treasure\GameMailInfo;
use Models\Treasure\GameMailInfoReceive;
use Modules\Client\Http\Requests\SendEmailRequest;
use Modules\Client\Transformers\GameMailInfoReceiveResource;
use Modules\Client\Transformers\GameMailInfoResource;

class EmailController extends Controller
{
    //用户发送邮件
    public function sendEmail(SendEmailRequest $request)
    {
        $res = GameMailInfoReceive::saveOne();
        if (!$res){
            return ResponeFails('发送失败');
        }
        //新的邮件消息后台推送
        MsgPush::dispatch(['type' => 'email']);
        return ResponeSuccess('发送成功');
    }

    //用户发送邮件列表
    public function sendEmailList()
    {
        $list = GameMailInfoReceive::where('UserID',\request('user_id'))
            ->where('IsDelete',0)->orderBy('ID','desc')->paginate(30);
        return ResponeSuccess('请求成功',GameMailInfoReceiveResource::collection($list));
    }

    //用户接收邮件列表
    public function receiveEmailList()
    {
        $list = GameMailInfo::where('UserID',\request('user_id'))
            ->where('IsDelete',0)->orderBy('IsRead','asc')->orderBy('ID','desc')->paginate(30);
        return ResponeSuccess('请求成功',GameMailInfoResource::collection($list));
    }

    //删除发件箱的邮件
    public function sendEmailDel()
    {
        $res = GameMailInfoReceive::where('UserID',\request('user_id'))->where('ID',\request('id'))->update(['IsDelete'=>1]);
        if (!$res){
            return ResponeFails('删除失败');
        }
        return ResponeSuccess('删除成功');
    }

}
