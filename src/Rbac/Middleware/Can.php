<?php

namespace DmitryBubyakin\Rbac\Middleware;


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
     * @param  string $redirect
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions, $require = true, $redirect = null)
    {
        $require = is_string($require) ? strcasecmp('true',(string)$require) === 0 : $require;
        if (Auth::guest() || !Auth::user()->can($permissions,$require)) {
            if($redirect){
                return redirect()->route($redirect);
            }
            abort(403);
        }

        return $next($request);
    }
}
