<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query\UseCases;

use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\Query\WorkspaceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QueryOneWorkspaceHandler
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function __invoke(QueryOneWorkspace $query): Workspace
    {
        return $this->workspaceRepository->get($query->uuid, $query->realmId);
    }
}
