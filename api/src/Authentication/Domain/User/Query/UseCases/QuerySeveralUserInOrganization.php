<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;

final readonly class QuerySeveralUserInOrganization
{
    public function __construct(
        public OrganizationId $organizationId,
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
