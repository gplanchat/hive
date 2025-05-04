<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command\UseCases;

use App\Authentication\Domain\Role\RoleId;

final readonly class DeleteRole
{
    public function __construct(
        public RoleId $roleId,
    ) {
    }
}
