<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query\UseCases;

use App\Authentication\Domain\Workspace\Query\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QuerySeveralWorkspaceInOrganizationHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(QuerySeveralWorkspaceInOrganization $query): WorkspacePage
    {
        return $this->workspaceRepository->listFromOrganization($query->organizationId, $query->currentPage, $query->itemsPerPage);
    }
}
