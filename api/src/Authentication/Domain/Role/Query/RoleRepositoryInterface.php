<?php

namespace App\Authentication\Domain\Role\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Query\UseCases\RolePage;
use App\Authentication\Domain\Role\RoleId;

interface RoleRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(RoleId $roleId, RealmId $realmId): Role;
    /** @return Role[] */
    public function getAll(RealmId $realmId, RoleId ...$roleIds): iterable;
    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): RolePage;
    /** @throws NotFoundException */
    public function listFromOrganization(RealmId $realmId, OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): RolePage;
}
