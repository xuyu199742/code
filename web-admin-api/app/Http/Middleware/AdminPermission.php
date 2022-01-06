<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Models\AdminPlatform\Menu;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class AdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param         $request
     * @param Closure $next
     *
     * @return mixed
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next)
    {
        $router_name = $request->route()->getName();
        $userGuard = Auth::guard('admin');
        $user = $userGuard->user();
        $Menu = new Menu();
        $response = $next($request);
        if ($user->super() || empty($router_name) || in_array($router_name, $Menu->getUserRules($user->id))) {
            return $response;
        }

        throw new AuthorizationException('没有权限访问', 200);
    }

    private function guard()
    {
        return Auth::guard('admin');
    }

    private function token()
    {
        /*   $time  = $this->guard()->getPayload()['exp'];
           $now   = time();
           $token = '';*/
        $token = $this->guard()->refresh();
        return 'bearer' . $token;

    }
}
