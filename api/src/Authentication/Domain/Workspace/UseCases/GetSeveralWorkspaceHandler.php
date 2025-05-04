<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\UseCases;

use App\Authentication\Domain\Workspace\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetSeveralWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(GetSeveralWorkspace $query): WorkspacePage
    {
        return $this->workspaceRepository->list($query->currentPage, $query->itemsPerPage);
    }
}
