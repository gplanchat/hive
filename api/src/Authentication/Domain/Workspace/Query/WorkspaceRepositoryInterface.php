<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Workspace\Query\UseCases\WorkspacePage;
use App\Authentication\Domain\Workspace\WorkspaceId;

interface WorkspaceRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(WorkspaceId $workspaceId, RealmId $realmId): Workspace;

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): WorkspacePage;

    /** @throws NotFoundException */
    public function listFromOrganization(RealmId $realmId, OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): WorkspacePage;
}
