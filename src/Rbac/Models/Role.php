<?php

namespace DmitryBubyakin\Rbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use DmitryBubyakin\Rbac\Contracts\Role as RoleInterface;
use DmitryBubyakin\Rbac\Contracts\Permission as PermissionInterface;

class Role extends Model implements RoleInterface
{
    protected $fillable = [
        'name', 'description',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('rbac.tables.role');
    }

    /**
     * Relation with the permission model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        $rolePermission = config('rbac.tables.role_permission');
        $roleFk         = config('rbac.foreign.role');
        $permissionFk   = config('rbac.foreign.permission');
        return $this->belongsToMany(Permission::class, $rolePermission, $roleFk, $permissionFk);
    }

    /**
     * Relation with the user model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        $userModel = config('rbac.models.user');
        $roleUser  = config('rbac.tables.role_user');
        $roleFk    = config('rbac.foreign.role');
        $userFk    = config('rbac.foreign.user');
        return $this->belongsToMany($userModel, $roleUser, $roleFk, $userFk);
    }

    /**
     * Get cached permissions if caching enabled
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions()
    {
        $enabled   = config('rbac.cache.enabled');
        $minutes   = config('rbac.cache.minutes');
        $key       = static::class.".rbac#{$this->getKey()}";
        if ($enabled) {
            return Cache::remember($key, $minutes, function () {
                return $this->permissions()->get();
            });
        }
        return $this->permissions()->get();
    }

    /**
     * Returns current role's permissions names
     * @return array
     */
    public function getPermissionNames()
    {
        $names = [];
        foreach ($this->getPermissions() as $permission) {
            $names[] = $permission->name;
        }
        return $names;
    }

    /**
     * Attach permission to the role
     * @param Permission|array|string $permission
     * @return void
     */
    public function attachPermission($permission)
    {
        if ($permission instanceof Permission) {
            $this->permissions()->attach($permission);
        } else if (is_string($permission)) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            $this->attachPermission($perm);
        } else if (is_array($permission)) {
            foreach ($permission as $perm) {
                $this->attachPermission($perm);
            }
        }
    }

    /**
     * Detach permission from the role
     * @param null|Permission|string|array $permission <p>
     * if $permission is null then all permissions will be detached from the role
     * </p>
     * @return void
     */
    public function detachPermission($permission = null)
    {
        if ($permission instanceof Permission) {
            $this->permissions()->detach($permission);
        } else if (is_string($permission)) {
            $perm = Permission::where(['name' => $permission])->first();
            if (!$perm) {
                return;
            }
            $this->detachPermission($perm);
        } else if (is_array($permission)) {
            foreach ($permission as $perm) {
                $this->detachPermission($perm);
            }
        } else if ($permission === null) {
            $this->permissions()->sync([]);
        }
    }

    /**
     * Check if current role has permissions
     * @param $permission
     * @param bool $requireAll <p>if false then role must contain at least one permission to return true</p>
     * @return bool
     */
    public function hasPermission($permission, $requireAll = true)
    {
        $perms = $this->getPermissions();
        if (is_string($permission)) {
            return $perms->contains('name', $permission);
        } else if ($permission instanceof PermissionInterface) {
            return $perms->contains($permission);
        } else if (is_array($permission)) {
            $total = count($permission);
            foreach ($permission as $perm) {
                $perm = $perm instanceof PermissionInterface ? $perm->name : $perm;
                if ($perms->contains('name', $perm)) {
                    $total--;
                    if (!$requireAll) {
                        return true;
                    }
                }
            }
            return !$total;
        }
    }
}
