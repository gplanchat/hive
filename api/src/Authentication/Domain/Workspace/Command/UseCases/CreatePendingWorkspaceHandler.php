<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Workspace\Command\Workspace;
use App\Authentication\Domain\Workspace\Command\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreatePendingWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(CreatePendingWorkspace $command): void
    {
        $workspace = Workspace::declareDisabled(
            $command->uuid,
            $command->realmId,
            $command->organizationId,
            $command->name,
            $command->slug,
        );

        $this->workspaceRepository->save($workspace);
    }
}
