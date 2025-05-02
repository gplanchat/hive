<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserPage;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\StorageMock;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(UserId $userId): User
    {
        $item = $this->storage->getItem("tests.data-fixtures.user.{$userId->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof User) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function list(int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $result = array_filter(
            $this->storage->getValues(),
            function (mixed $value): bool {
                return $value instanceof User;
            }
        );

        return new UserPage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromOrganization(OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $result = array_filter(
            array_filter(
                $this->storage->getValues(),
                function (mixed $value): bool {
                    return $value instanceof User;
                }
            ),
            fn (User $user) => $user->organizationId->equals($organizationId),
        );

        return new UserPage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromWorkspace(WorkspaceId $workspaceId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $result = array_filter(
            array_filter(
                $this->storage->getValues(),
                function (mixed $value): bool {
                    return $value instanceof User;
                }
            ),
            fn (User $user) => [] !== array_filter(
                $user->workspaceIds,
                fn (WorkspaceId $current) => $current->equals($workspaceId)
            ),
        );

        return new UserPage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
