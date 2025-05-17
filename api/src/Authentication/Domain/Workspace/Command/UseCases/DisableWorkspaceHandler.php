<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Workspace\Command\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DisableWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(DisableWorkspace $command): void
    {
        $workspace = $this->workspaceRepository->get($command->uuid, $command->realmId);

        $workspace->disable($command->validUntil);

        $this->workspaceRepository->save($workspace);
    }
}
