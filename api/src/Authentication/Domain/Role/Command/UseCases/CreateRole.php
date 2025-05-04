<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\RoleId;

final readonly class CreateRole
{
    /** @param ResourceAccess[] $resourceAccesses */
    public function __construct(
        public RoleId $uuid,
        public OrganizationId $organizationId,
        public string $identifier,
        public string $label,
        public array $resourceAccesses,
    ) {
    }
}
