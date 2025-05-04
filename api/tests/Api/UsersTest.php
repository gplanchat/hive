<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;
use App\Authentication\Infrastructure\StorageMock;
use Psr\Clock\ClockInterface;

class UsersTest extends ApiTestCase
{
    static ?bool $alwaysBootKernel = false;

    private ?ClockInterface $clock = null;
    private ?OrganizationFixtures $organizationFixtures = null;
    private ?UserFixtures $userFixtures = null;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->clock = self::getContainer()->get(ClockInterface::class);
        assert($this->clock instanceof ClockInterface);

        $this->userFixtures = new UserFixtures(
            self::getContainer()->get(StorageMock::class)
        );
        assert($this->userFixtures instanceof UserFixtures);
        $this->userFixtures->load();

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

        $this->userFixtures->unload();
        $this->userFixtures = null;

        $this->clock = null;

        parent::tearDown();
    }

    /** @test */
    public function itShouldListUsers(): void
    {
        $this->userFixtures->load();

        static::createClient()->request('GET', '/authentication/users');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => '/authentication/users',
            '@type' => 'Collection',
            'totalItems' => 6,
        ]);
    }

    /** @test */
    public function itShouldShowAnUser(): void
    {
        static::createClient()->request('GET', '/authentication/users/01966c5a-10ef-7abd-9c88-52b075bcae99');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            '@id' => '/authentication/users/01966c5a-10ef-7abd-9c88-52b075bcae99',
            'uuid' => '01966c5a-10ef-7abd-9c88-52b075bcae99',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
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
        static::createClient()->request('POST', '/authentication/users', [
            'json' => [
                'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'workspaceIds' => [
                    '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
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
        static::createClient()->request('POST', '/authentication/users', [
            'json' => [
                'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
                'workspaceIds' => [
                    '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
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
        static::createClient()->request('POST', '/authentication/users', [
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
    public function itShouldCreateAnEnabledUserWithinAnOrganization(): void
    {
        $this->markTestIncomplete();

        static::createClient()->request('POST', '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users', [
            'json' => [
                'workspaceIds' => [
                    '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
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
        $this->markTestIncomplete();

        static::createClient()->request('POST', '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users', [
            'json' => [
                'workspaceIds' => [
                    '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                    '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
                ],
                'roleIds' => [
                    '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                    '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
                ],
                'username' => 'wile.coyote',
                'firstName' => 'Wile',
                'lastName' => 'E. Coyote',
                'email' => 'wile.coyote@example.com',
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
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
        $this->markTestIncomplete();

        static::createClient()->request('POST', '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8/users', [
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
    public function itShouldEnableADisabledUser(): void
    {
        static::createClient()->request('PATCH', '/authentication/users/01966c5a-10ef-7040-9576-09078df3ea8a/enable', [
            'json' => [
                'enabled' => true,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            '@id' => '/authentication/users/01966c5a-10ef-7040-9576-09078df3ea8a',
            'uuid' => '01966c5a-10ef-7040-9576-09078df3ea8a',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-76f6-9513-e3b858c22f0a',
            'workspaceIds' => [
                '/authentication/workspaces/01966c5a-10ef-7f9c-8c9f-80657a996b9d',
                '/authentication/workspaces/01966c5a-10ef-70ce-ab8c-c455e874c3fc',
                '/authentication/workspaces/01966c5a-10ef-7795-9e13-7359dd58b49c',
            ],
            'roleIds' => [
                '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
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
        static::createClient()->request('PATCH', '/authentication/users/01966c5a-10ef-7abd-9c88-52b075bcae99/disable', [
            'json' => [
                'enabled' => false,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            '@id' => '/authentication/users/01966c5a-10ef-7abd-9c88-52b075bcae99',
            'uuid' => '01966c5a-10ef-7abd-9c88-52b075bcae99',
            'organizationId' => '/authentication/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'workspaceIds' => [
                '/authentication/workspaces/01966c5a-10ef-723c-bc33-2b1dc30d8963',
                '/authentication/workspaces/01966cc2-0323-7a38-9da3-3aeea904ea49',
            ],
            'roleIds' => [
                '/authentication/roles/01966d41-78eb-7406-ad99-03ad025e8bcf',
                '/authentication/roles/01966d41-a4a3-7cd4-a095-be712f2e724a',
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
        static::createClient()->request('DELETE', '/authentication/users/01966c5a-10ef-7040-9576-09078df3ea8a', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
