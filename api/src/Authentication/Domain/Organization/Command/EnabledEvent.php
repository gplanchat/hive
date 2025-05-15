<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;

final readonly class EnabledEvent
{
    public function __construct(
        public OrganizationId $uuid,
        public int $version,
        public RealmId $realmId,
        public ?\DateTimeInterface $validUntil = null,
    ) {
    }
}
