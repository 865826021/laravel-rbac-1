<?php

namespace DmitryBubyakin\RBAC\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  string|array $roles
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        if (Auth::guest() || !Auth::user()->roleIs($roles)) {
            abort(403);
        }
        return $next($request);
    }
}
