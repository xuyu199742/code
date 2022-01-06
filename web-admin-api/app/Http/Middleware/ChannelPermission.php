<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class ChannelPermission
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param                          $roles
     *
     * @return mixed
     * @throws
     */
    public function handle($request, Closure $next, $roles)
    {
        $user  = Auth::guard('channel')->user();
        $roles = explode('|', $roles);
        if (in_array($user->getRole(), $roles)) {
            return $next($request);
        }
        throw new AuthorizationException('没有权限访问', 200);
    }
}
