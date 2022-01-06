<?php

namespace Modules\Channel\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;

class BaseController extends Controller
{
    protected $guard = 'channel';
    /**
     * 通过渠道id获取渠道子集合
     * @param   bool    $isme           是否包含自己：默认包含true,不包含为false
     *
     */
    protected function getChannelIds($pid, $isme = true)
    {
        $ChannelInfo = new ChannelInfo();
        $list = $ChannelInfo->getIdOrPid(2);//获取所有渠道
        return getTreeRegroup($list, $pid, $isme);//获取递归后的数据
    }
    /**
     * 通过渠道id获取用户id集合
     * @param   bool    $type           类型：true为集合，false为个人
     * @param   bool    $isme           是否包含自己：默认包含true,不包含为false
     *
     */
    protected function getChannelUserIds($pid, $type = true, $isme = true)
    {
        $ChannelUser = new ChannelUserRelation();
        if ($type === false){
            $channel_id = $pid;
        }else{
            $channel_id = $this->getChannelIds($pid, $isme);
        }
        return $ChannelUser->getUserIds($channel_id);
    }
    //登录渠道后台的id
    protected function channel_id()
    {
         return (Auth::guard($this->guard)->id())??0;
    }

}
