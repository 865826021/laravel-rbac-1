<?php

namespace DmitryBubyakin\RBAC\Traits;


use DmitryBubyakin\RBAC\Models\Permission;
use DmitryBubyakin\RBAC\Models\Role;
use Illuminate\Support\Facades\Cache;

trait UserTrait
{
    /**
     * Relations with the role model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        $rolePermission = config('rbac.tables.role_user');
        $roleFk         = config('rbac.foreign.role');
        $userFk         = config('rbac.foreign.permission');
        return $this->belongsToMany(Role::class, $rolePermission, $userFk, $roleFk);
    }


    /**
     * Get cached roles if caching enabled
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoles()
    {
        $enabled   = config('rbac.cache.enabled');
        $namespace = config('rbac.cache.namespace');
        $minutes   = config('rbac.cache.minutes');
        $key       = "$namespace.role#{$this->getKey()}";
        if ($enabled) {
            return Cache::remember($key, $minutes, function () {
                return $this->roles()->get();
            });
        }
        return $this->roles()->get();
    }

    /**
     * Returns current user's roles with permissions
     * @return array
     */
    public function getRolePermissionsNames()
    {
        $names = [];
        foreach ($this->getRoles() as $role) {
            $names[$role->name] = $role->getPermissionNames();
        }
        return $names;
    }

    /**
     * Attach role to current user
     * @param Role|string|array $role
     * @return void
     */
    public function attachRole($role)
    {
        if ($role instanceof Role) {
            $this->roles()->attach($role);
        } else if (is_string($role)) {
            $model = Role::firstOrCreate(['name' => $role]);
            $this->attachRole($model);
        } else if (is_array($role)) {
            foreach ($role as $r) {
                $this->attachRole($r);
            }
        }
    }

    /**
     * Detach role from current user
     * @param null|Role|string|array $role <p>
     * if $role is null then all roles will be detached from current user
     * </p>
     * @return void
     */
    public function detachRole($role = null)
    {
        if ($role instanceof Role) {
            $this->roles()->detach($role);
        } else if (is_string($role)) {
            $model = Role::firstOrCreate(['name' => $role]);
            $this->detachRole($model);
        } else if (is_array($role)) {
            foreach ($role as $r) {
                $this->detachRole($r);
            }
        } else if ($role === null) {
            $this->roles()->sync([]);
        }
    }

    /**
     * Check user role
     * @param string|Role|array $role
     * @param bool $requireAll
     * @return bool
     */
    public function roleIs($role, $requireAll = true)
    {
        $roles = $this->getRoles();
        $role = $this->delimited($role);

        if (is_string($role)) {
            return $roles->contains('name', $role);
        } else if ($role instanceof Role) {
            return $roles->contains($role);
        } else if (is_array($role)) {
            $total = count($role);
            $count = 0;
            foreach ($role as $r) {
                $r = $r instanceof Role ? $r->name : $r;
                if ($roles->contains('name', $r)) {
                    $count++;
                    if (!$requireAll) {
                        break;
                    }
                }
            }
            return $requireAll ? $count === $total : $count > 0;
        }
        return false;
    }


    /**
     * Check if current user can
     * @param $permission
     * @param bool $requireAll
     * @return bool
     */
    public function can($permission, $requireAll = true)
    {
        $roles      = $this->getRoles();
        $permission = $this->delimited($permission);

        if (is_string($permission) || $permission instanceof Permission) {
            return $this->traverse($roles, $permission);
        } else if (is_array($permission)) {
            $total = count($permission);
            $count = 0;
            foreach ($permission as $perm) {
                if ($this->traverse($roles, $perm)) {
                    $count++;
                    if (!$requireAll) {
                        break;
                    }
                }
            }
            return $requireAll ? $total === $count : $count > 0;
        }

        return false;
    }

    /**
     * @param $roles \Illuminate\Database\Eloquent\Collection
     * @param $permission
     * @return bool
     */
    protected function traverse($roles, $permission)
    {
        foreach ($roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param $haystack
     * @param string $delimiter
     * @return array|string
     */
    protected function delimited($haystack, $delimiter = '|')
    {
        return str_contains($haystack, $delimiter) ? explode($delimiter, $haystack) : $haystack;
    }

}