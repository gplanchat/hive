<?php

namespace App\Authentication\Domain\Organization\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\UseCases\OrganizationPage;

interface OrganizationRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(OrganizationId $organizationId): Organization;
    public function list(int $currentPage = 1, int $pageSize = 25): OrganizationPage;
}
