<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\RoleId;

final readonly class DeclaredEvent
{
    /**
     * @param ResourceAccess[] $resourceAccesses
     */
    public function __construct(
        public RoleId $uuid,
        public int $version,
        public RealmId $realmId,
        public OrganizationId $organizationId,
        public string $identifier,
        public string $label,
        public array $resourceAccesses = [],
    ) {
    }
}
