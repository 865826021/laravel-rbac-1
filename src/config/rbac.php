<?php

return [
    'models' => [
        'role' => 'App\Role',
        'user' => 'App\User',
        'permission' => 'App\Permission',
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