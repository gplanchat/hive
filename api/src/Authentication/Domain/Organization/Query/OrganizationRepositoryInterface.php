<?php

namespace App\Authentication\Domain\Organization\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\UseCases\OrganizationPage;
use App\Authentication\Domain\Realm\RealmId;

interface OrganizationRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(OrganizationId $organizationId, RealmId $realmId): Organization;
    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): OrganizationPage;
}
