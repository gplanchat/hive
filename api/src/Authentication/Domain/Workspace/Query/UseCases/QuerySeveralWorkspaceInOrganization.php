<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;

final readonly class QuerySeveralWorkspaceInOrganization
{
    public function __construct(
        public RealmId $realmId,
        public OrganizationId $organizationId,
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
