<?php

namespace DmitryBubyakin\Rbac\Middleware;


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
     * @param  string $redirect
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $require = true, $redirect = null)
    {
        $require = $require == 'true';
        if (Auth::guest() || !Auth::user()->roleIs($roles, $require)) {
            if ($redirect) {
                return redirect()->route($redirect);
            }
            abort(403);
        }
        return $next($request);
    }
}
