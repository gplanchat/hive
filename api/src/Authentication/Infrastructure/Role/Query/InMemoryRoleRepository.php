<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\Actions;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use App\Authentication\Domain\Role\Query\UseCases\RolePage;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\Resources;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Infrastructure\StorageMock;


final class InMemoryRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(RoleId $roleId): Role
    {
        $item = $this->storage->getItem("tests.data-fixtures.role.{$roleId->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof Role) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function list(int $currentPage = 1, int $pageSize = 25): RolePage
    {
        $result = array_filter(
            $this->storage->getValues(),
            function (mixed $value): bool {
                return $value instanceof Role;
            }
        );

        return new RolePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public function listFromOrganization(OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): RolePage
    {
        $result = array_filter(
            array_filter(
                $this->storage->getValues(),
                function (mixed $value): bool {
                    return $value instanceof Role;
                }
            ),
            fn (Role $role) => $role->organizationId->equals($organizationId),
        );

        return new RolePage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
