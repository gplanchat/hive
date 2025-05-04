<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\UseCases\WorkspacePage;
use App\Authentication\Domain\Workspace\Workspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Domain\Workspace\WorkspaceRepositoryInterface;

final class InMemoryWorkspaceRepository implements WorkspaceRepositoryInterface
{
    private array $storage = [];

    public function __construct(
        Workspace ...$workspaces,
    ) {
        $this->storage = $workspaces;
    }

    public function get(WorkspaceId $workspaceId): Workspace
    {
        $result = array_filter($this->storage, fn (Workspace $workspace) => $workspace->uuid->equals($workspaceId));

        return array_shift($result) ?? throw new NotFoundException();
    }

    public function list(int $currentPage = 1, int $pageSize = 25): WorkspacePage
    {
        $result = $this->storage;

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
            $this->storage,
            fn (Workspace $workspace) => $workspace->organizationId->equals($organizationId),
        );

        return new WorkspacePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public static function buildTestRepository(): self
    {
        return new self(
            new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-723c-bc33-2b1dc30d8963'),
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
            ),
            new Workspace(
                WorkspaceId::fromString('01966cc2-0323-7a38-9da3-3aeea904ea49'),
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
            ),
            new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-7328-8638-39bf546a5bf4'),
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
            ),
            new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-7f9c-8c9f-80657a996b9d'),
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
            ),
            new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-70ce-ab8c-c455e874c3fc'),
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
            ),
            new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-7795-9e13-7359dd58b49c'),
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
            ),
        );
    }
}
