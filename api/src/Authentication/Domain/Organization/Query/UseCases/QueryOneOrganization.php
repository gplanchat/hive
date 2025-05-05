<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Query\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;

final readonly class QueryOneOrganization
{
    public function __construct(
        public OrganizationId $uuid,
    ) {
    }
}
