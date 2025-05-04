<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\UseCases;

use App\Authentication\Domain\Workspace\Workspace;
use App\Authentication\Domain\Workspace\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetOneWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(GetOneWorkspace $query): Workspace
    {
        return $this->workspaceRepository->get($query->uuid);
    }
}
