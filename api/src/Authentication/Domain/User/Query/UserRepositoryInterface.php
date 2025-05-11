<?php

namespace App\Authentication\Domain\User\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\UseCases\UserPage;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;

interface UserRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(UserId $userId, RealmId $realmId): User;
    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): UserPage;
    /** @throws NotFoundException */
    public function listFromOrganization(RealmId $realmId, OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): UserPage;
    /** @throws NotFoundException */
    public function listFromWorkspace(RealmId $realmId, WorkspaceId $workspaceId, int $currentPage = 1, int $pageSize = 25): UserPage;
}
