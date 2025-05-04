<?php

namespace App\Authentication\Domain\Role\Command;

use App\Authentication\Domain\Role\RoleId;

interface RoleRepositoryInterface
{
    public function get(
        RoleId $roleId,
    ): Role;

    public function save(
        Role $role,
    ): void;
}
