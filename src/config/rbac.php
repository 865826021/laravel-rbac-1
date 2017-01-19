<?php

return [
    'models' => [
        'role' => DmitryBubyakin\RBAC\Models\Role::class,
        'user' => 'App\Models\User',
        'permission' => DmitryBubyakin\RBAC\Models\Permission::class,
    ],
    'tables' => [
        'role' => 'roles',
        'permission' => 'permissions',
        'role_user' => 'role_user',
        'role_permission' => 'role_permission',
    ],
    'foreign' => [
        'role' => 'role_id',
        'permission' => 'permission_id',
        'user' => 'user_id',
    ],
    'cache' => [
        'enabled' => true,
        'namespace' => 'rbac',
        'minutes' => 1,
    ],
];