<?php

namespace App\Authentication\Domain\Role\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\Query\UseCases\RolePage;
use App\Authentication\Domain\Role\RoleId;

interface RoleRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(RoleId $roleId): Role;
    public function list(int $currentPage = 1, int $pageSize = 25): RolePage;
    /** @throws NotFoundException */
    public function listFromOrganization(OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): RolePage;
}
