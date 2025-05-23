<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Workspace\Command\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class EnableWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }
    public function __invoke(EnableWorkspace $command): void
    {
        $workspace = $this->workspaceRepository->get($command->uuid);

        $workspace->enable($command->validUntil);

        $this->workspaceRepository->save($workspace);
    }
}
