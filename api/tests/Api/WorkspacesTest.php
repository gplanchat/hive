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
use App\Authentication\Infrastructure\Workspace\DataFixtures\WorkspaceFixtures;
use Psr\Clock\ClockInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class WorkspacesTest extends ApiTestCase
{
    public static ?bool $alwaysBootKernel = false;

    private ?ClockInterface $clock = null;
    private ?OrganizationFixtures $organizationFixtures = null;
    private ?WorkspaceFixtures $workspaceFixtures = null;
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

        $this->workspaceFixtures = new WorkspaceFixtures(
            $this->clock,
            self::getContainer()->get(StorageMock::class)
        );
        \assert($this->workspaceFixtures instanceof WorkspaceFixtures);
        $this->workspaceFixtures->load();

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

        $this->workspaceFixtures->unload();
        $this->workspaceFixtures = null;

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
    public function itShouldListWorkspaces(): void
    {
        $this->workspaceFixtures->load();

        static::createClient()->request('GET', '/authentication/acme-inc/workspaces', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@id' => '/authentication/acme-inc/workspaces',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 6,
        ]);
    }

    /** @test */
    public function itShouldListWorkspacesInOrganization(): void
    {
        $this->workspaceFixtures->load();

        static::createClient()->request('GET', '/authentication/acme-inc/organizations/01966c5a-10ef-76f6-9513-e3b858c22f0a/workspaces', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@id' => '/authentication/acme-inc/organizations/01966c5a-10ef-76f6-9513-e3b858c22f0a/workspaces',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
        ]);
    }

    /** @test */
    public function itShouldShowAWorkspace(): void
    {
        static::createClient()->request('GET', '/authentication/acme-inc/workspaces/01966c5a-10ef-70ce-ab8c-c455e874c3fc', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@type' => 'Workspace',
            '@id' => '/authentication/acme-inc/workspaces/01966c5a-10ef-70ce-ab8c-c455e874c3fc',
            'uuid' => '01966c5a-10ef-70ce-ab8c-c455e874c3fc',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-76f6-9513-e3b858c22f0a',
        ]);
    }

    /** @test */
    public function itShouldCreateAnEnabledWorkspace(): void
    {
        $validUntil = $this->clock->now()->add(new \DateInterval('P3M2D'))->format('Y-m-d');

        static::createClient()->request('POST', '/authentication/acme-inc/workspaces', [
            'json' => [
                'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'name' => 'Project 101',
                'slug' => 'project-101',
                'validUntil' => $validUntil,
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@type' => 'Workspace',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'name' => 'Project 101',
            'slug' => 'project-101',
            'validUntil' => $validUntil,
            'enabled' => true,
        ]);
    }

    /** @test */
    public function itShouldCreateAPendingWorkspace(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/workspaces', [
            'json' => [
                'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'name' => 'Project 101',
                'slug' => 'project-101',
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@type' => 'Workspace',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'name' => 'Project 101',
            'slug' => 'project-101',
            'enabled' => false,
        ]);
    }

    /** @test */
    public function itShouldRespondBadRequestOnIncompletePayloadOnCreation(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/workspaces', [
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
    public function itShouldCreateAnEnabledWorkspaceWithinAnOrganization(): void
    {
        $validUntil = $this->clock->now()->add(new \DateInterval('P3M2D'))->format('Y-m-d');

        static::createClient()->request('POST', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/workspaces', [
            'json' => [
                'name' => 'Project 101',
                'slug' => 'project-101',
                'validUntil' => $validUntil,
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@type' => 'Workspace',
            'name' => 'Project 101',
            'slug' => 'project-101',
            'validUntil' => $validUntil,
            'enabled' => true,
        ]);
    }

    /** @test */
    public function itShouldCreateAPendingWorkspaceWithinAnOrganization(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/workspaces', [
            'json' => [
                'name' => 'Project 101',
                'slug' => 'project-101',
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@type' => 'Workspace',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'name' => 'Project 101',
            'slug' => 'project-101',
            'enabled' => false,
        ]);
    }

    /** @test */
    public function itShouldRespondBadRequestOnIncompletePayloadOnCreationWithinAnOrganization(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/workspaces', [
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
    public function itShouldEnableADisabledWorkspace(): void
    {
        $validUntil = $this->clock->now()->add(new \DateInterval('P3M2D'))->format('Y-m-d');

        static::createClient()->request('PATCH', '/authentication/acme-inc/workspaces/01966c5a-10ef-7795-9e13-7359dd58b49c/enable', [
            'json' => [
                'enabled' => true,
                'validUntil' => $validUntil,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@type' => 'Workspace',
            '@id' => '/authentication/acme-inc/workspaces/01966c5a-10ef-7795-9e13-7359dd58b49c',
            'uuid' => '01966c5a-10ef-7795-9e13-7359dd58b49c',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'name' => 'Consectetur adipiscing elit',
            'slug' => 'consectetur-adipiscing-elit',
            'validUntil' => $validUntil,
            'enabled' => true,
        ]);
    }

    /** @test */
    public function itShouldDisableAnEnabledWorkspace(): void
    {
        $validUntil = $this->clock->now()->add(new \DateInterval('P3M2D'))->format('Y-m-d');

        static::createClient()->request('PATCH', '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963/disable', [
            'json' => [
                'enabled' => false,
                'validUntil' => $validUntil,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Workspace',
            '@type' => 'Workspace',
            '@id' => '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
            'uuid' => '01966c5a-10ef-723c-bc33-2b1dc30d8963',
            'organizationId' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'name' => 'Lorem ipsum',
            'slug' => 'lorem-ipsum',
            'validUntil' => $validUntil,
            'enabled' => false,
        ]);
    }

    /** @test */
    public function itShouldDeleteAWorkspace(): void
    {
        static::createClient()->request('DELETE', '/authentication/acme-inc/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
