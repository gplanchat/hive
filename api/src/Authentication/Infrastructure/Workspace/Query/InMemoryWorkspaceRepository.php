<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\Query\UseCases\WorkspacePage;
use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\Query\WorkspaceRepositoryInterface;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\StorageMock;

final class InMemoryWorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(WorkspaceId $workspaceId): Workspace
    {
        $item = $this->storage->getItem("tests.data-fixtures.workspace.{$workspaceId->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof Workspace) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function list(int $currentPage = 1, int $pageSize = 25): WorkspacePage
    {
        $result = array_filter(
            $this->storage->getValues(),
            function (mixed $value): bool {
                return $value instanceof Workspace;
            }
        );

        return new WorkspacePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromOrganization(OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): WorkspacePage
    {
        $result = array_filter(
            array_filter(
                $this->storage->getValues(),
                function (mixed $value): bool {
                    return $value instanceof Workspace;
                }
            ),
            fn (Workspace $workspace) => $workspace->organizationId->equals($organizationId),
        );

        return new WorkspacePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
