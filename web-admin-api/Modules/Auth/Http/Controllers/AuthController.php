<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Models\AdminPlatform\AdminUser;
use Models\AdminPlatform\LoginLog;
use Modules\Auth\Http\Requests\AddAdminUserRequest;
use Modules\Auth\Http\Requests\ResetAdminUserRequest;
use Transformers\AdminUserTransformer;
use Google2FA;
use Illuminate\Support\Facades\Crypt;
use Validator;


class AuthController extends Controller
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
     * @throws
     */
    public function login(Request $request)
    {

        Validator::make($request->all(), [
            'mobile'   => ['required'],
            'password' => ['required'],
            'captcha'  => ['required'],
        ], [
            'mobile.required'   => '用户名不能为空',
            'password.required' => '密码不能为空',
            'captcha.required'  => '动态密令不能为空',
        ])->validate();

        $credentials = $request->only('mobile', 'password');
        if ($token = $this->guard()->attempt($credentials)) {
            $user = $this->guard()->user();
            //判断超管是否被禁用
            if ($user->super() && config('super.forbidden') == true) {
                return ResponeFails('登录失败:账号不存在');
            }
            //如果谷歌验证器关闭的情况
            if (env('GOOLGE_LOGIN', false) === false) {
                $this->setCacheToken($token, $user->id);
                return $this->respondWithToken($token);
            }

            //超管首次登录，没有绑定验证器，判断密令是否是首次超管登录密令
            if (empty($user->google2fa_secret) && $user->super() && $request->input('captcha') == config('google2fa.first_login_captcha')) {
                $this->setCacheToken($token, $user->id);
                return $this->respondWithToken($token);
            }

            //非超管登录
            if (empty($user->google2fa_secret) && !$user->super()) {
                $partent_admin = AdminUser::find($user->admin_id);
                if ($partent_admin) {
                    $secret = Crypt::decrypt($partent_admin->google2fa_secret);
                    if (Google2FA::verifyKey($secret, $request->input('captcha'))) {
                        $this->setCacheToken($token, $user->id);
                        return $this->respondWithToken($token);
                    }
                }
                $this->guard()->logout();
                return ResponeFails('登录失败:上级密令错误');
            }
            if ($user->google2fa_secret) {
                $secret = Crypt::decrypt($user->google2fa_secret);
                if (Google2FA::verifyKey($secret, $request->input('captcha'))) {
                    $this->setCacheToken($token, $user->id);
                    return $this->respondWithToken($token);
                }
                $this->guard()->logout();
                return ResponeFails('登录失败:动态密令错误');
            } else {
                $this->guard()->logout();
                return ResponeFails('登录失败:密令不存在');
            }
        }
        return ResponeFails('登录失败:账号、密码、验证码错误');
    }


    /**
     * 获取个人信息
     *
     * @return mixed
     */
    public function me()
    {
        return $this->response->item($this->guard()->user(), new AdminUserTransformer());
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
        $token = $this->guard()->refresh();
        $user_id = $this->guard()->user()->id ?? '';
        $this->handelOldToken($user_id, $token);
        return $this->respondWithToken($token);
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
        LoginLog::addLogs();
        return $this->response->array([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $this->guard()->factory()->getTTL() * 60,
            'permission'   => $this->user()->getAllPermissions()->pluck('name'),
            'is_admin'     => $this->user()->super(),
            'user_id'      => $this->user()->id
        ]);
    }

    public function setCacheToken($token, $user_id)
    {
        $this->handelOldToken($user_id, $token);
    }

    /**
     * @return mixed
     */
    public function guard()
    {
        return Auth::guard($this->guard);
    }

    /**
     * 修改个人信息和密码
     *
     * @param ResetAdminUserRequest $request
     */
    public function reset(ResetAdminUserRequest $request)
    {
        $id = Auth::id();
        $model = AdminUser::find($id);
        if ($model) {
            $model->fill($request->all());
            $model->password = Hash::make($request->input('password'));
            try {
                if ($model->save()) {
                    $this->guard()->logout();
                    return ResponeSuccess('修改成功');
                    //return $this->response->array(['message' => '修改成功']);
                }
            } catch (\Exception $e) {
                return ResponeFails('保存用户信息失败');
                //return $this->response->errorInternal('保存用户信息失败');
            }
        }
        return ResponeFails('用户不存在');
        //return $this->response->errorNotFound('用户不存在');
    }

    public function addUser(AddAdminUserRequest $request)
    {
        $model = new AdminUser();
        $action = '添加';
        $old_password = '';
        if ($request->input('id')) {
            $model = AdminUser::find($request->input('id'));
            if (!$model) {
                return ResponeFails('用户不存在');
            }
            $action = '修改';
            $old_password = $model->password;
        }
        $model->fill($request->all());
        $model->admin_id = $this->user()->id;
        if ($request->input('password')) {
            $model->password = Hash::make($request->input('password'));
        } else {
            $model->password = $old_password;
        }
        if ($model->save()) {
            return ResponeSuccess($action . '成功');
        }
        return ResponeFails($action . '失败');
    }

    public function handelOldToken($user_id, $token)
    {
        $userGuard = clone Auth::guard('admin');
        if ($oldToken = \Cache::get('token_' . $user_id)) {
            $userGuard->setToken($oldToken);
            if ($userGuard->check()) {
                $userGuard->invalidate();
            }
        }
        if (!in_array($user_id, explode(',',  env('EXCLUDE_ADMIN_USER_LOGIN', false)))) {
            \Cache::put('token_' . $user_id , $token, env('JWT_TTL', 60) * 60);
        }
    }
}
