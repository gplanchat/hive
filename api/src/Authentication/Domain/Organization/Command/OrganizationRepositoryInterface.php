<?php

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;

interface OrganizationRepositoryInterface
{
    public function get(
        OrganizationId $organizationId,
        RealmId $realmId,
    ): Organization;

    public function save(
        Organization $organization,
    ): void;
}
