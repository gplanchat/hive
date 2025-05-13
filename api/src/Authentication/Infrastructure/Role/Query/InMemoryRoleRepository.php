<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Actions;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use App\Authentication\Domain\Role\Query\UseCases\RolePage;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\Resources;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use App\Shared\Infrastructure\Collection\Collection;


final class InMemoryRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(RoleId $roleId, RealmId $realmId): Role
    {
        $item = $this->storage->getItem(RoleFixtures::buildCacheKey($roleId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof Role) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function getAll(RealmId $realmId, RoleId ...$roleIds): Collection
    {
        return Collection::fromArray($roleIds)
            ->map(fn (RoleId $roleId) => $this->get($roleId, $realmId));
    }

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): RolePage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof Role)
            ->filter(fn (Role $role): bool => $role->realmId->equals($realmId))
            ->toArray();

        return new RolePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromOrganization(RealmId $realmId, OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): RolePage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof Role)
            ->filter(fn (Role $role): bool => $role->realmId->equals($realmId))
            ->filter(fn (Role $role) => $role->organizationId->equals($organizationId))
            ->toArray();

        return new RolePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
