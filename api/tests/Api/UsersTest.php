<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;
use App\Authentication\Infrastructure\Keycloak\KeycloakMock;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;
use Psr\Clock\ClockInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class UsersTest extends ApiTestCase
{
    public static ?bool $alwaysBootKernel = false;

    private ?ClockInterface $clock = null;
    private ?OrganizationFixtures $organizationFixtures = null;
    private ?UserFixtures $userFixtures = null;
    private ?RoleFixtures $roleFixtures = null;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $clock = self::getContainer()->get(ClockInterface::class);
        \assert($clock instanceof ClockInterface);
        $this->clock = $clock;

        $storageMock = self::getContainer()->get(StorageMock::class);
        \assert($storageMock instanceof StorageMock);

        $this->roleFixtures = new RoleFixtures($storageMock);
        \assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->load();

        $this->userFixtures = new UserFixtures($storageMock);
        \assert($this->userFixtures instanceof UserFixtures);
        $this->userFixtures->load();

        $this->organizationFixtures = new OrganizationFixtures($this->clock, $storageMock);
        \assert($this->organizationFixtures instanceof OrganizationFixtures);
        $this->organizationFixtures->load();
    }

    protected function tearDown(): void
    {
        \assert($this->organizationFixtures instanceof OrganizationFixtures);
        $this->organizationFixtures->unload();
        $this->organizationFixtures = null;

        \assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->unload();
        $this->roleFixtures = null;

        \assert($this->userFixtures instanceof UserFixtures);
        $this->userFixtures->unload();
        $this->userFixtures = null;

        $this->clock = null;

        parent::tearDown();
    }

    private static function getTokenFor(string $username): string
    {
        $keycloak = self::getContainer()->get(KeycloakInterface::class);
        \assert($keycloak instanceof KeycloakMock);

        return $keycloak->generateJWT($username);
    }

    /** @test */
    public function itShouldListUsers(): void
    {
        static::createClient()->request('GET', '/authentication/acme-inc/users', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => '/authentication/acme-inc/users',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 6,
        ]);
    }

    /** @test */
    public function itShouldListUsersFromOrganization(): void
    {
        static::createClient()->request('GET', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
        ]);
    }

    /** @test */
    public function itShouldListUsersFromWorkspace(): void
    {
        static::createClient()->request('GET', '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963/users', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963/users',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
        ]);
    }

    /** @test */
    public function itShouldShowAnUser(): void
    {
        static::createClient()->request('GET', '/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            '@id' => '/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99',
            'uuid' => '01966c5a-10ef-7abd-9c88-52b075bcae99',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
            ],
            'username' => 'john.doe',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'enabled' => true,
        ]);
    }

    /** @test */
    public function itShouldCreateAnEnabledUser(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/users', [
            'json' => [
                'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'workspaceIds' => [
                    '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
            ],
            'username' => 'wile.coyote',
            'firstName' => 'Wile',
            'lastName' => 'E. Coyote',
            'email' => 'wile.coyote@example.com',
            'enabled' => true,
        ]);
    }

    /** @test */
    public function itShouldCreateAPendingUser(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/users', [
            'json' => [
                'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'workspaceIds' => [
                    '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
            ],
            'username' => 'wile.coyote',
            'firstName' => 'Wile',
            'lastName' => 'E. Coyote',
            'email' => 'wile.coyote@example.com',
            'enabled' => false,
        ]);
    }

    /** @test */
    public function itShouldRespondBadRequestOnIncompletePayloadOnCreation(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/users', [
            'json' => [
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            '@id' => '/errors/400',
            '@type' => 'hydra:Error',
            'title' => 'An error occurred',
        ]);
    }

    /** @test */
    public function itShouldCreateAnEnabledUserWithinAnOrganization(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users', [
            'json' => [
                'workspaceIds' => [
                    '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
            ],
            'username' => 'wile.coyote',
            'firstName' => 'Wile',
            'lastName' => 'E. Coyote',
            'email' => 'wile.coyote@example.com',
            'enabled' => true,
        ]);
    }

    /** @test */
    public function itShouldCreateAPendingUserWithinAnOrganization(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users', [
            'json' => [
                'workspaceIds' => [
                    '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
            ],
            'username' => 'wile.coyote',
            'firstName' => 'Wile',
            'lastName' => 'E. Coyote',
            'email' => 'wile.coyote@example.com',
            'enabled' => false,
        ]);
    }

    /** @test */
    public function itShouldRespondBadRequestOnIncompletePayloadOnCreationWithinAnOrganization(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users', [
            'json' => [
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            '@id' => '/errors/400',
            '@type' => 'hydra:Error',
            'title' => 'An error occurred',
        ]);
    }

    /** @test */
    public function itShouldEnableADisabledUser(): void
    {
        static::createClient()->request('PATCH', '/authentication/acme-inc/users/01966c5a-10ef-7040-9576-09078df3ea8a/enable', [
            'json' => [
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            '@id' => '/authentication/acme-inc/users/01966c5a-10ef-7040-9576-09078df3ea8a',
            'uuid' => '01966c5a-10ef-7040-9576-09078df3ea8a',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-76f6-9513-e3b858c22f0a',
            'workspaceIds' => [
                '/authentication/acme-inc/workspaces/01966c5a-10ef-7f9c-8c9f-80657a996b9d',
                '/authentication/acme-inc/workspaces/01966c5a-10ef-70ce-ab8c-c455e874c3fc',
                '/authentication/acme-inc/workspaces/01966c5a-10ef-7795-9e13-7359dd58b49c',
            ],
            'roleIds' => [
                '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
            ],
            'username' => 'clara.froelich',
            'firstName' => 'Clara',
            'lastName' => 'Froelich',
            'email' => 'clara.froelich@example.com',
            'enabled' => true,
        ]);
    }

    /** @test */
    public function itShouldDisableAnEnabledUser(): void
    {
        static::createClient()->request('PATCH', '/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99/disable', [
            'json' => [
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            '@id' => '/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99',
            'uuid' => '01966c5a-10ef-7abd-9c88-52b075bcae99',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/acme-inc/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/acme-inc/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
            ],
            'username' => 'john.doe',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'enabled' => false,
        ]);
    }

    /** @test */
    public function itShouldDeleteAnUser(): void
    {
        static::createClient()->request('DELETE', '/authentication/acme-inc/users/01966c5a-10ef-7040-9576-09078df3ea8a', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
