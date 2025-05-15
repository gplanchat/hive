<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;

final readonly class EnableOrganization
{
    public function __construct(
        public OrganizationId $uuid,
        public RealmId $realmId,
        public ?\DateTimeInterface $validUntil = null,
    ) {
    }
}
