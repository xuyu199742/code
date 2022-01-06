<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Models\Accounts\AccountsFace;
use Models\Accounts\AccountsInfo;
use Models\Accounts\UserLevel;

class Controller extends BaseController
{
    protected $guard = 'admin';

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Helpers;

    public function success($message)
    {
        return $this->response->array(['message' => $message]);
    }

    public function user(){
        return Auth::guard('admin')->user();
    }

    /**
     * 获取管理员id
     *
     */
    protected function getMasterId()
    {
        return 1;
    }

    /**
     * 通过game_id获取user_id的条件查询
     *
     * @param   int         $game_id            玩家游戏id
     * @param   object      $obj                当前查询对象
     * @param   string      $user_id_key        当前查询条件的key值，默认UserID
     *
     */
    protected function gameIdSearchUserId($game_id, $obj, $user_id_key = 'UserID')
    {
        if ($game_id){
            $AccountsInfo = new AccountsInfo();
            $user_id = $AccountsInfo->getUserId($game_id);
            if ($user_id){
                return $obj->where($user_id_key,$user_id);
            }
            return $obj->whereNull($user_id_key);
        }
        return $obj;
    }

    /**
     * 通过user_id获取game_id
     *
     * @param   int     $user_id        用户user_id
     *
     */
    protected function getGameId($user_id)
    {
        try{
            $AccountsInfo = AccountsInfo::find($user_id);
            return $AccountsInfo->GameID;
        }catch (\Exception $exception){
            return 0;
        }
    }

    public function checkUser($user_id)
    {
        $user = AccountsFace::where('UserID', $user_id)->first();
        if(!$user) {
            return ResponeFails('该用户不存在');
        }
    }

    /**
     * 检查该用户VIP等级功能是否开启
     * @param $user_id
     * @param $type
     * @return boolean
     */
    public function checkUserLevelConfig($user_id, $type)
    {
        $userInfo = AccountsInfo::query()->where('UserID', $user_id)->first();
        if(!UserLevel::query()->where('LevelID', $userInfo->MemberOrder)->value($type)) {
            return false;
        }
        return true;
    }

    public function monitor()
    {
        return Response::json('ok');
    }

}
