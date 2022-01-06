<?php

namespace Modules\Channel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Models\Agent\ChannelInfo;

class ChannelAuthController extends BaseController
{


    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:' . $this->guard, ['except' => ['login']]);
    }

    /**
     * 管理员登录
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    public function login(Request $request)
    {
        $credentials = ['phone' => $request->input('mobile'), 'password' => $request->input('password')];
        if ($token = $this->guard()->attempt($credentials)) {
            if ($this->guard()->user()->nullity == ChannelInfo::NULLITY_ON) {
                return $this->respondWithToken($token);
            }
            return ResponeFails('登录失败:该渠道已被禁用');
            $this->guard()->logout();
        }
        return ResponeFails('登录失败:账号或密码错误');
    }


    /**
     * 获取个人信息
     *
     * @return mixed
     */
    public function me()
    {
        $info         = $this->guard()->user()->toArray();
        $info['role'] = [$this->guard()->user()->getRole()];
        return ResponeSuccess('查询成功', $info);
    }


    /**
     * 退出登录
     *
     * @return mixed
     */
    public function logout()
    {
        $this->guard()->logout();
        return $this->response->array(['message' => '退出成功']);
    }


    /**
     * 刷新token
     *
     * @return mixed
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }


    /**
     * 返回token
     *
     * @param $token
     *
     * @return mixed
     */
    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $this->guard()->factory()->getTTL() * 60,
            'role'         => [$this->guard()->user()->getRole()]
        ]);
    }


    /**
     * @return mixed
     */
    public function guard()
    {
        return Auth::guard($this->guard);
    }

}
