<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Workspace\Command\Workspace;
use App\Authentication\Domain\Workspace\Command\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateEnabledWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }
    public function __invoke(CreateEnabledWorkspace $command): void
    {
        $workspace = Workspace::declareEnabled(
            $command->uuid,
            $command->organizationId,
            $command->name,
            $command->slug,
            $command->validUntil,
        );

        $this->workspaceRepository->save($workspace);
    }
}
