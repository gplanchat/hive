<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\Organization\OrganizationId;

final readonly class EnabledEvent
{
    public function __construct(
        public OrganizationId $uuid,
        public int $version,
        public ?\DateTimeInterface $validUntil = null,
    ) {
    }
}
