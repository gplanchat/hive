<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\DataFixtures;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\Actions;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\Resources;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Infrastructure\StorageMock;
use Symfony\Contracts\Cache\ItemInterface;

final class RoleFixtures
{
    const TAG = 'tests.data-fixtures.role';

    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function load(): void
    {
        $adminRoles = [
            new ResourceAccess(
                Resources::Organization,
                Actions::List,
                Actions::Show,
            ),
            new ResourceAccess(
                Resources::Workspace,
                Actions::List,
                Actions::Show,
                Actions::Create,
                Actions::Update,
                Actions::Delete,
            ),
            new ResourceAccess(
                Resources::Role,
                Actions::List,
                Actions::Show,
                Actions::Create,
                Actions::Update,
                Actions::Delete,
            ),
            new ResourceAccess(
                Resources::User,
                Actions::List,
                Actions::Show,
                Actions::Create,
                Actions::Update,
                Actions::Delete,
            ),
        ];

        $userRoles = [
            new ResourceAccess(
                Resources::Organization,
                Actions::List,
                Actions::Show,
            ),
            new ResourceAccess(
                Resources::Workspace,
                Actions::List,
                Actions::Show,
            ),
            new ResourceAccess(
                Resources::Role,
                Actions::List,
                Actions::Show,
            ),
            new ResourceAccess(
                Resources::User,
                Actions::List,
                Actions::Show,
            ),
        ];


        $this->storage->get('tests.data-fixtures.role.01966d41-78eb-7406-ad99-03ad025e8bcf', function (ItemInterface $item) use ($adminRoles) {
            $item->tag([self::TAG]);

            return new Role(
                RoleId::fromString('01966d41-78eb-7406-ad99-03ad025e8bcf'),
                'ROLE_ADMIN',
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
                $adminRoles,
            );
        });

        $this->storage->get('tests.data-fixtures.role.01969388-78d2-7e96-a08b-ca9e83aee2d9', function (ItemInterface $item) use ($userRoles) {
            $item->tag([self::TAG]);

            return new Role(
                RoleId::fromString('01969388-78d2-7e96-a08b-ca9e83aee2d9'),
                'ROLE_USER',
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
                $userRoles,
            );
        });

        $this->storage->get('tests.data-fixtures.role.01969388-78d2-7f92-9ef2-2322011f4a72', function (ItemInterface $item) use ($adminRoles) {
            $item->tag([self::TAG]);

            return new Role(
                RoleId::fromString('01969388-78d2-7f92-9ef2-2322011f4a72'),
                'ROLE_ADMIN',
                OrganizationId::fromString('01966c5a-10ef-77a1-b158-d4356966e1ab'),
                $adminRoles,
            );
        });

        $this->storage->get('tests.data-fixtures.role.01966d41-a4a3-7cd4-a095-be712f2e724a', function (ItemInterface $item) use ($userRoles) {
            $item->tag([self::TAG]);

            return new Role(
                RoleId::fromString('01966d41-a4a3-7cd4-a095-be712f2e724a'),
                'ROLE_USER',
                OrganizationId::fromString('01966c5a-10ef-77a1-b158-d4356966e1ab'),
                $userRoles,
            );
        });

        $this->storage->get('tests.data-fixtures.role.01969388-78d2-7fb0-8c61-51ecbf98d41c', function (ItemInterface $item) use ($adminRoles) {
            $item->tag([self::TAG]);

            return new Role(
                RoleId::fromString('01969388-78d2-7fb0-8c61-51ecbf98d41c'),
                'ROLE_ADMIN',
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
                $adminRoles,
            );
        });

        $this->storage->get('tests.data-fixtures.role.01969388-78d2-7530-bd4d-d7673bce9f34', function (ItemInterface $item) use ($userRoles) {
            $item->tag([self::TAG]);

            return new Role(
                RoleId::fromString('01969388-78d2-7530-bd4d-d7673bce9f34'),
                'ROLE_USER',
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
                $userRoles,
            );
        });
    }

    public function unload(): void
    {
    }
}
