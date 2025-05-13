<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;
use App\Authentication\Infrastructure\Keycloak\KeycloakMock;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;
use Psr\Clock\ClockInterface;

class OrganizationsTest extends ApiTestCase
{
    static ?bool $alwaysBootKernel = false;

    private ?ClockInterface $clock = null;
    private ?OrganizationFixtures $organizationFixtures = null;
    private ?UserFixtures $userFixtures = null;
    private ?RoleFixtures $roleFixtures = null;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->clock = self::getContainer()->get(ClockInterface::class);
        assert($this->clock instanceof ClockInterface);

        $this->roleFixtures = new RoleFixtures(
            self::getContainer()->get(StorageMock::class)
        );
        assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->load();

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

        $this->roleFixtures->unload();
        $this->roleFixtures = null;

        $this->clock = null;

        parent::tearDown();
    }

    private static function getTokenFor(string $username): string
    {
        $keycloak = self::getContainer()->get(KeycloakInterface::class);
        assert($keycloak instanceof KeycloakMock);

        return $keycloak->generateJWT($username);
    }

    /** @test */
    public function itShouldListOrganizations(): void
    {
        $this->organizationFixtures->load();

        static::createClient()->request('GET', '/authentication/acme-inc/organizations', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@id' => '/authentication/acme-inc/organizations',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
        ]);
    }

    /** @test */
    public function itShouldShowAnOrganization(): void
    {
        static::createClient()->request('GET', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@type' => 'Organization',
            '@id' => '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'uuid' => '01966c5a-10ef-7315-94f2-cbeec2f167d8',
            'name' => 'Gyroscops',
            'slug' => 'gyroscops',
            'enabled' => true,
            'validUntil' => $this->clock->now()->add(new \DateInterval('P3M2D'))->format('Y-m-d'),
            'featureRolloutIds' => [
                '/feature-rollouts/role.principal-administrator',
                '/feature-rollouts/subscription.enterprise',
                '/feature-rollouts/demo.lorem-ipsum',
                '/feature-rollouts/demo.dolor-sit-amet',
                '/feature-rollouts/demo.consecutir-sid',
            ],
        ]);
    }

    /** @test */
    public function itShouldCreateAnEnabledOrganization(): void
    {
        $validUntil = $this->clock->now()->add(new \DateInterval('P4M12D'));

        static::createClient()->request('POST', '/authentication/acme-inc/organizations', [
            'json' => [
                'name' => 'Wile E. Coyote Ltd.',
                'slug' => 'wile-e-coyote-ltd',
                'enabled' => true,
                'featureRolloutIds' => [
                    '/feature-rollouts/subscription.enterprise',
                    '/feature-rollouts/demo.lorem-ipsum',
                ],
                'validUntil' => $validUntil->format('Y-m-d'),
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@type' => 'Organization',
            'name' => 'Wile E. Coyote Ltd.',
            'slug' => 'wile-e-coyote-ltd',
            'enabled' => true,
            'featureRolloutIds' => [
                '/feature-rollouts/subscription.enterprise',
                '/feature-rollouts/demo.lorem-ipsum',
            ],
            'validUntil' => $validUntil->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function itShouldCreateAPendingOrganization(): void
    {
        static::createClient()->request('POST', '/authentication/acme-inc/organizations', [
            'json' => [
                'name' => 'Acme Inc.',
                'slug' => 'acme-inc',
                'enabled' => false,
                'featureRolloutIds' => [
                    '/feature-rollouts/subscription.enterprise',
                    '/feature-rollouts/demo.lorem-ipsum',
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@type' => 'Organization',
            'name' => 'Acme Inc.',
            'slug' => 'acme-inc',
            'enabled' => false,
            'featureRolloutIds' => [
                '/feature-rollouts/subscription.enterprise',
                '/feature-rollouts/demo.lorem-ipsum',
            ],
        ]);
    }

    /** @test */
    public function itShouldRespondBadRequestOnIncompletePayloadOnCreation(): void
    {
        $validUntil = $this->clock->now()->add(new \DateInterval('P4M12D'));

        static::createClient()->request('POST', '/authentication/acme-inc/organizations', [
            'json' => [
                'featureRolloutIds' => [
                    '/feature-rollouts/subscription.enterprise',
                    '/feature-rollouts/demo.lorem-ipsum',
                ],
                'validUntil' => $validUntil->format('Y-m-d'),
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
    public function itShouldEnableADisabledOrganization(): void
    {
        $validUntil = $this->clock->now()->add(new \DateInterval('P4M12D'));

        static::createClient()->request('PATCH', '/authentication/acme-inc/organizations/01966c5a-10ef-76f6-9513-e3b858c22f0a/enable', [
            'json' => [
                'enabled' => true,
                'validUntil' => $validUntil->format('Y-m-d'),
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@type' => 'Organization',
            'name' => 'Big Corp.',
            'slug' => 'big-corp',
            'enabled' => true,
            'validUntil' => $validUntil->format('Y-m-d'),
            'featureRolloutIds' => [
                '/feature-rollouts/subscription.enterprise',
                '/feature-rollouts/demo.lorem-ipsum',
                '/feature-rollouts/demo.dolor-sit-amet',
            ],
        ]);
    }

    /** @test */
    public function itShouldDisableAnEnabledOrganization(): void
    {
        static::createClient()->request('PATCH', '/authentication/acme-inc/organizations/01966c5a-10ef-77a1-b158-d4356966e1ab/disable', [
            'json' => [
                'enabled' => false,
                'validUntil' => null,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@type' => 'Organization',
            'name' => 'ACME Inc.',
            'slug' => 'acme-inc',
            'enabled' => false,
            'validUntil' => null,
            'featureRolloutIds' => [
                '/feature-rollouts/subscription.enterprise',
                '/feature-rollouts/demo.lorem-ipsum',
                '/feature-rollouts/demo.dolor-sit-amet',
            ],
        ]);
    }

    /** @test */
    public function itShouldAddSomeFeatureRollouts(): void
    {
        static::createClient()->request('PATCH', '/authentication/acme-inc/organizations/01966c5a-10ef-77a1-b158-d4356966e1ab/add-features', [
            'json' => [
                'featureRolloutIds' => [
                    '/feature-rollouts/demo.this-can-be-added',
                    '/feature-rollouts/demo.this-can-also-be-added',
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@type' => 'Organization',
            'name' => 'ACME Inc.',
            'slug' => 'acme-inc',
            'enabled' => true,
            'featureRolloutIds' => [
                '/feature-rollouts/subscription.enterprise',
                '/feature-rollouts/demo.lorem-ipsum',
                '/feature-rollouts/demo.dolor-sit-amet',
                '/feature-rollouts/demo.consecutir-sid',
                '/feature-rollouts/demo.this-can-be-added',
                '/feature-rollouts/demo.this-can-also-be-added',
            ],
        ]);
    }

    /** @test */
    public function itShouldRemoveSomeFeatureRollouts(): void
    {
        static::createClient()->request('PATCH', '/authentication/acme-inc/organizations/01966c5a-10ef-77a1-b158-d4356966e1ab/remove-features', [
            'json' => [
                'featureRolloutIds' => [
                    '/feature-rollouts/demo.lorem-ipsum',
                    '/feature-rollouts/demo.dolor-sit-amet',
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Organization',
            '@type' => 'Organization',
            'name' => 'ACME Inc.',
            'slug' => 'acme-inc',
            'enabled' => true,
            'featureRolloutIds' => [
                '/feature-rollouts/subscription.enterprise',
            ],
        ]);
    }

    /** @test */
    public function itShouldDeleteAnOrganization(): void
    {
        static::createClient()->request('DELETE', '/authentication/acme-inc/organizations/01966c5a-10ef-77a1-b158-d4356966e1ab', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
