<?php

namespace DmitryBubyakin\Rbac\Middleware;


use Closure;
use DmitryBubyakin\Rbac\Traits\Except;
use Illuminate\Support\Facades\Auth;

class Role
{
    use Except;

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
        if ($this->excepted()) {
            return $next($request);
        }

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
