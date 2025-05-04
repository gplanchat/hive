<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;

final readonly class GetSeveralWorkspaceInOrganization
{
    public function __construct(
        public OrganizationId $organizationId,
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
