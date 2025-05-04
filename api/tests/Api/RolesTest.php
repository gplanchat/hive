<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use Psr\Clock\ClockInterface;

class RolesTest extends ApiTestCase
{
    static ?bool $alwaysBootKernel = false;

    private ?ClockInterface $clock = null;
    private ?OrganizationFixtures $organizationFixtures = null;
    private ?RoleFixtures $roleFixtures = null;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->clock = self::getContainer()->get(ClockInterface::class);
        assert($this->clock instanceof ClockInterface);

        $this->roleFixtures = new RoleFixtures(
            self::getContainer()->get(StorageMock::class)
        );
        assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->load();

        $this->organizationFixtures = new OrganizationFixtures(
            $this->clock,
            self::getContainer()->get(StorageMock::class)
        );
        assert($this->organizationFixtures instanceof OrganizationFixtures);
        $this->organizationFixtures->load();
    }

    public function tearDown(): void
    {
        $this->organizationFixtures->unload();
        $this->organizationFixtures = null;

        $this->roleFixtures->unload();
        $this->roleFixtures = null;

        $this->clock = null;

        parent::tearDown();
    }

    /** @test */
    public function itShouldListRoles(): void
    {
        $this->roleFixtures->load();

        static::createClient()->request('GET', '/authentication/roles');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@id' => '/authentication/roles',
            '@type' => 'Collection',
            'totalItems' => 6,
        ]);
    }

    /** @test */
    public function itShouldShowARole(): void
    {
        static::createClient()->request('GET', '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@type' => 'Role',
            '@id' => '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
            'uuid' => '01966d41-78eb-7406-ad99-03ad025e8bcf',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
        ]);
    }

    /** @test */
    public function itShouldCreateARole(): void
    {
        static::createClient()->request('POST', '/authentication/roles', [
            'json' => [
                'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'identifier' => 'manager',
                'label' => 'Manager',
                'resourceAccesses' => [],
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Role',
            '@type' => 'Role',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'identifier' => 'manager',
            'label' => 'Manager',
            'resourceAccesses' => [],
        ]);
    }

    /** @test */
    public function itShouldRespondBadRequestOnIncompletePayloadOnCreation(): void
    {
        static::createClient()->request('POST', '/authentication/roles', [
            'json' => [
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            '@id' => '/errors/400',
            '@type' => 'Error',
            'title' => 'An error occurred',
        ]);
    }

    /** @test */
    public function itShouldDeleteARole(): void
    {
        static::createClient()->request('DELETE', '/authentication/roles/01969388-78d2-7530-bd4d-d7673bce9f34', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
