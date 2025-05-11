<?php

namespace App\Authentication\Domain\Role\Command;

use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\RoleId;

interface RoleRepositoryInterface
{
    public function get(
        RoleId $roleId,
        RealmId $realmId,
    ): Role;

    public function save(
        Role $role,
    ): void;
}
