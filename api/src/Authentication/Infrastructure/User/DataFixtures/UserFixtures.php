<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\DataFixtures;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\Keycloak\KeycloakAuthorization;
use App\Authentication\Infrastructure\Keycloak\KeycloakUserId;
use App\Authentication\Infrastructure\StorageMock;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class UserFixtures
{
    public const TAG = 'tests.data-fixtures.user';

    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public static function buildCacheKey(UserId $userId, RealmId $realmId): string
    {
        return "tests.data-fixtures.{$realmId->toString()}.user.{$userId->toString()}";
    }

    private function with(User $user): void
    {
        $this->storage->get(self::buildCacheKey($user->uuid, $user->realmId), function (ItemInterface $item) use ($user): User {
            $item->tag([self::TAG]);

            return $user;
        });
    }

    public function load(): void
    {
        $this->with(new User(
            UserId::fromString('01966c5a-10ef-7abd-9c88-52b075bcae99'),
            RealmId::fromString('acme-inc'),
            new KeycloakAuthorization(
                KeycloakUserId::fromString('01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ),
            OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
            workspaceIds: [
                WorkspaceId::fromString('01966c5a-10ef-723c-bc33-2b1dc30d8963'),
                WorkspaceId::fromString('01966cc2-0323-7a38-9da3-3aeea904ea49'),
            ],
            roleIds: [
                RoleId::fromString('01966d41-78eb-7406-ad99-03ad025e8bcf'),
                RoleId::fromString('01966d41-a4a3-7cd4-a095-be712f2e724a'),
            ],
            username: 'john.doe',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            enabled: true,
        ));

        $this->with(new User(
            UserId::fromString('01966c5a-10ef-7c83-9881-4ce08f0116f4'),
            RealmId::fromString('acme-inc'),
            new KeycloakAuthorization(
                KeycloakUserId::fromString('01966c5a-10ef-7c83-9881-4ce08f0116f4'),
            ),
            OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
            workspaceIds: [
                WorkspaceId::fromString('01966c5a-10ef-723c-bc33-2b1dc30d8963'),
                WorkspaceId::fromString('01966cc2-0323-7a38-9da3-3aeea904ea49'),
            ],
            roleIds: [
                RoleId::fromString('01966d41-a4a3-7cd4-a095-be712f2e724a'),
            ],
            username: 'jane.doe',
            firstName: 'Jane',
            lastName: 'Doe',
            email: 'jane.doe@example.com',
            enabled: true,
        ));

        $this->with(new User(
            UserId::fromString('01966c5a-10ef-750c-9228-d41d6f3e33a1'),
            RealmId::fromString('acme-inc'),
            new KeycloakAuthorization(
                KeycloakUserId::fromString('01966c5a-10ef-750c-9228-d41d6f3e33a1'),
            ),
            OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
            workspaceIds: [
                WorkspaceId::fromString('01966c5a-10ef-723c-bc33-2b1dc30d8963'),
                WorkspaceId::fromString('01966cc2-0323-7a38-9da3-3aeea904ea49'),
            ],
            roleIds: [
                RoleId::fromString('01966d41-a4a3-7cd4-a095-be712f2e724a'),
            ],
            username: 'ada.lovelace',
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'ada.lovelace@example.com',
            enabled: true,
        ));

        $this->with(new User(
            UserId::fromString('01966c5a-10ef-7670-971d-e6e600135a73'),
            RealmId::fromString('acme-inc'),
            new KeycloakAuthorization(
                KeycloakUserId::fromString('01966c5a-10ef-7670-971d-e6e600135a73'),
            ),
            OrganizationId::fromString('01966c5a-10ef-77a1-b158-d4356966e1ab'),
            workspaceIds: [
                WorkspaceId::fromString('01966c5a-10ef-7328-8638-39bf546a5bf4'),
            ],
            roleIds: [
                RoleId::fromString('01966d41-78eb-7406-ad99-03ad025e8bcf'),
                RoleId::fromString('01966d41-a4a3-7cd4-a095-be712f2e724a'),
            ],
            username: 'beatrice.cave-brown-cave',
            firstName: 'Beatrice',
            lastName: 'Cave-Brown-Cave',
            email: 'beatrice.cave-brown-cave@example.com',
            enabled: true,
        ));

        $this->with(new User(
            UserId::fromString('01966c5a-10ef-7d6f-a6cd-a74560cea954'),
            RealmId::fromString('acme-inc'),
            new KeycloakAuthorization(
                KeycloakUserId::fromString('01966c5a-10ef-7d6f-a6cd-a74560cea954'),
            ),
            OrganizationId::fromString('01966c5a-10ef-77a1-b158-d4356966e1ab'),
            workspaceIds: [
                WorkspaceId::fromString('01966c5a-10ef-7328-8638-39bf546a5bf4'),
            ],
            roleIds: [
                RoleId::fromString('01966d41-a4a3-7cd4-a095-be712f2e724a'),
            ],
            username: 'mary.clem',
            firstName: 'Mary',
            lastName: 'Clem',
            email: 'mary.clem@example.com',
            enabled: true,
        ));

        $this->with(new User(
            UserId::fromString('01966c5a-10ef-7040-9576-09078df3ea8a'),
            RealmId::fromString('acme-inc'),
            new KeycloakAuthorization(
                KeycloakUserId::fromString('01966c5a-10ef-7040-9576-09078df3ea8a'),
            ),
            OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
            workspaceIds: [
                WorkspaceId::fromString('01966c5a-10ef-7f9c-8c9f-80657a996b9d'),
                WorkspaceId::fromString('01966c5a-10ef-70ce-ab8c-c455e874c3fc'),
                WorkspaceId::fromString('01966c5a-10ef-7795-9e13-7359dd58b49c'),
            ],
            roleIds: [
                RoleId::fromString('01966d41-78eb-7406-ad99-03ad025e8bcf'),
                RoleId::fromString('01966d41-a4a3-7cd4-a095-be712f2e724a'),
            ],
            username: 'clara.froelich',
            firstName: 'Clara',
            lastName: 'Froelich',
            email: 'clara.froelich@example.com',
            enabled: false,
        ));
    }

    public function unload(): void
    {
        $this->storage->invalidateTags([self::TAG]);
    }
}
