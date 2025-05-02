<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\Organization\OrganizationId;

final readonly class DeletedEvent
{
    public function __construct(
        public OrganizationId $uuid,
        public int $version,
    ) {
    }
}
