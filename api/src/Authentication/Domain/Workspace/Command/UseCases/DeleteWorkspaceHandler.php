<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Workspace\Command\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final class DeleteWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(DeleteWorkspace $command): void
    {
        $organization = $this->workspaceRepository->get($command->uuid, $command->realmId);
        $organization->delete();
        $this->workspaceRepository->save($organization);
    }
}
