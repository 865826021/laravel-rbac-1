<?php

namespace DmitryBubyakin\RBAC\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;

class Can
{


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  string|array $permissions
     * @param  bool $require
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions, $require = true)
    {

        if (Auth::guest() || !Auth::user()->can($permissions,$require)) {
            abort(403);
        }

        return $next($request);
    }
}
