<?php

namespace App\Authentication\Domain\User\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;

interface UserRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(UserId $userId): User;
    public function list(int $currentPage = 1, int $pageSize = 25): UserPage;
    /** @throws NotFoundException */
    public function listFromOrganization(OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): UserPage;
    /** @throws NotFoundException */
    public function listFromWorkspace(WorkspaceId $workspaceId, int $currentPage = 1, int $pageSize = 25): UserPage;
}
