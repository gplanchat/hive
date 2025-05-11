<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Workspace\Query\UseCases\WorkspacePage;
use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\Query\WorkspaceRepositoryInterface;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\Workspace\DataFixtures\WorkspaceFixtures;

final class InMemoryWorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(WorkspaceId $workspaceId, RealmId $realmId): Workspace
    {
        $item = $this->storage->getItem(WorkspaceFixtures::buildCacheKey($workspaceId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof Workspace) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): WorkspacePage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof Workspace)
            ->filter(fn (Workspace $workspace) => $workspace->realmId->equals($realmId))
            ->toArray();

        return new WorkspacePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromOrganization(RealmId $realmId, OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): WorkspacePage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof Workspace)
            ->filter(fn (Workspace $workspace) => $workspace->realmId->equals($realmId))
            ->filter(fn (Workspace $workspace) => $workspace->organizationId->equals($organizationId))
            ->toArray();

        return new WorkspacePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
