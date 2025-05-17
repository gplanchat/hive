<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\UseCases\UserPage;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;
use App\Platform\Infrastructure\Collection\Collection;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(UserId $userId, RealmId $realmId): User
    {
        $item = $this->storage->getItem(UserFixtures::buildCacheKey($userId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof User) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof User)
            ->filter(fn (User $user): bool => $user->realmId->equals($realmId))
            ->toArray()
        ;

        return new UserPage(
            $currentPage,
            $pageSize,
            \count($result),
            ...\array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromOrganization(RealmId $realmId, OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof User)
            ->filter(fn (User $user): bool => $user->realmId->equals($realmId))
            ->filter(fn (User $user): bool => $user->organizationId->equals($organizationId))
            ->toArray()
        ;

        return new UserPage(
            $currentPage,
            $pageSize,
            \count($result),
            ...\array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromWorkspace(RealmId $realmId, WorkspaceId $workspaceId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof User)
            ->filter(fn (User $user): bool => $user->realmId->equals($realmId))
            ->filter(fn (User $user): bool => Collection::fromArray($user->workspaceIds)->any(fn (WorkspaceId $current) => $current->equals($workspaceId)))
            ->toArray()
        ;

        return new UserPage(
            $currentPage,
            $pageSize,
            \count($result),
            ...\array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
