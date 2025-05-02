<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;

final readonly class DeleteOrganization
{
    public function __construct(
        public OrganizationId $uuid,
    ) {
    }
}
