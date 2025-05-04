<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command;

use App\Authentication\Domain\Role\RoleId;

final readonly class DeletedEvent
{
    public function __construct(
        public RoleId $uuid,
        public int $version,
    ) {
    }
}
