<?php

namespace DmitryBubyakin\Rbac\Contracts;


interface Role
{
    public function getPermissions();

    public function permissions();

    public function users();

    public function attachPermission($permission);

    public function detachPermission($permission);

    public function hasPermission($permission);
}
