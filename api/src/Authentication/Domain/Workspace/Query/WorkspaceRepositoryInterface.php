<?php

namespace App\Authentication\Domain\Workspace\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\Query\UseCases\WorkspacePage;
use App\Authentication\Domain\Workspace\WorkspaceId;

interface WorkspaceRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(WorkspaceId $workspaceId): Workspace;
    public function list(int $currentPage = 1, int $pageSize = 25): WorkspacePage;
    /** @throws NotFoundException */
    public function listFromOrganization(OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): WorkspacePage;
}
