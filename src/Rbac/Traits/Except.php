<?php

namespace DmitryBubyakin\Rbac\Traits;


use DmitryBubyakin\Rbac\Models\Permission;
use DmitryBubyakin\Rbac\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait Except
{
    /**
     * True if role excepted, otherwise false
     * @return bool
     */
    public function excepted()
    {
        if (!($user = Auth::user())) {
            return false;
        }

        $except = config('rbac.middleware.except');

        foreach ($user->getRoles() as $role) {
            if (in_array($role->name, $except)) {
                return true;
            }
        }

        return false;
    }

}