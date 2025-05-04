<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\UseCases;

use App\Authentication\Domain\Workspace\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetSeveralWorkspaceInOrganizationHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(GetSeveralWorkspaceInOrganization $query): WorkspacePage
    {
        return $this->workspaceRepository->listFromOrganization($query->organizationId, $query->currentPage, $query->itemsPerPage);
    }
}
