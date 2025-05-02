<?php

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\Organization\OrganizationId;

interface OrganizationRepositoryInterface
{
    public function get(
        OrganizationId $organizationId,
    ): Organization;

    public function save(
        Organization $organization,
    ): void;
}
