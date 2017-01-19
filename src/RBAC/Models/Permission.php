<?php

namespace DmitryBubyakin\RBAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use DmitryBubyakin\RBAC\Contracts\Permission as PermissionInterface;

class Permission extends Model implements PermissionInterface
{
    protected $fillable = [
        'name', 'description',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('rbac.tables.permission');
    }

    /**
     * Relations with the role model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        $rolePermission = Config::get('rbac.tables.role_permission');
        $roleFk         = Config::get('rbac.foreign.role');
        $permissionFk   = Config::get('rbac.foreign.permission');
        return $this->belongsToMany(Role::class, $rolePermission, $permissionFk, $roleFk);
    }
}
