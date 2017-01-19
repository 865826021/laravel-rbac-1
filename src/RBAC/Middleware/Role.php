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
     * @param  bool $require
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $require = true)
    {
        if (Auth::guest() || !Auth::user()->roleIs($roles,$require)) {
            abort(403);
        }
        return $next($request);
    }
}
