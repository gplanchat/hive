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
class RolesTest extends ApiTestCase
{
    public static ?bool $alwaysBootKernel = false;

    private ?ClockInterface $clock = null;
    private ?OrganizationFixtures $organizationFixtures = null;
    private ?UserFixtures $userFixtures = null;
    private ?RoleFixtures $roleFixtures = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->clock = self::getContainer()->get(ClockInterface::class);
        \assert($this->clock instanceof ClockInterface);

        $this->roleFixtures = new RoleFixtures(
            self::getContainer()->get(StorageMock::class)
        );
        \assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->load();

        $this->userFixtures = new UserFixtures(
            self::getContainer()->get(StorageMock::class)
        );
        \assert($this->userFixtures instanceof UserFixtures);
        $this->userFixtures->load();

        $this->organizationFixtures = new OrganizationFixtures(
            $this->clock,
            self::getContainer()->get(StorageMock::class)
        );
        \assert($this->organizationFixtures instanceof OrganizationFixtures);
        $this->organizationFixtures->load();
    }

    protected function tearDown(): void
    {
        $this->organizationFixtures->unload();
        $this->organizationFixtures = null;

        $this->userFixtures->unload();
        $this->userFixtures = null;

        $this->roleFixtures->unload();
        $this->roleFixtures = null;

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
    public function itShouldListRoles(): void
    {
        $this->roleFixtures->load();

        static::createClient()->request('GET', '/authentication/acme-inc/roles', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@id' => '/authentication/acme-inc/roles',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 6,
        ]);
    }

    /** @test */
    public function itShouldListRolesFromOrganization(): void
    {
        $this->roleFixtures->load();

        static::createClient()->request('GET', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/roles', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@id' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/roles',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 2,
        ]);
    }

    /** @test */
    public function itShouldShowARole(): void
    {
        static::createClient()->request('GET', '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@type' => 'Role',
            '@id' => '/authentication/acme-inc/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
            'uuid' => '01966d41-78eb-7406-ad99-03ad025e8bcf',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
        ]);
    }

    /** @test */
    public function itShouldCreateARole(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/roles', [
            'json' => [
                'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'identifier' => 'manager',
                'label' => 'Manager',
                'resourceAccesses' => [],
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@type' => 'Role',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'identifier' => 'manager',
            'label' => 'Manager',
            'resourceAccesses' => [],
        ]);
    }

    /** @test */
    public function itShouldCreateARoleInOrganization(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/roles', [
            'json' => [
                'identifier' => 'manager',
                'label' => 'Manager',
                'resourceAccesses' => [],
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@type' => 'Role',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'identifier' => 'manager',
            'label' => 'Manager',
            'resourceAccesses' => [],
        ]);
    }

    /** @test */
    public function itShouldRespondBadRequestOnIncompletePayloadOnCreation(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/roles', [
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
    public function itShouldDeleteARole(): void
    {
        static::createClient()->request('DELETE', '/authentication/acme-inc/roles/01969388-78d2-7530-bd4d-d7673bce9f34', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
