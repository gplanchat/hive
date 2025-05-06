<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\StorageMock;
use Psr\Clock\ClockInterface;

class OrganizationsTest extends ApiTestCase
{
    static ?bool $alwaysBootKernel = false;

    private ?ClockInterface $clock = null;
    private ?OrganizationFixtures $organizationFixtures = null;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->clock = self::getContainer()->get(ClockInterface::class);
        assert($this->clock instanceof ClockInterface);

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

        $this->clock = null;

        parent::tearDown();
    }

    /** @test */
    public function itShouldListOrganizations(): void
    {
        $this->organizationFixtures->load();

        static::createClient()->request('GET', '/authentication/acme-inc/organizations');

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
        static::createClient()->request('GET', '/authentication/acme-inc/organizations/01966c5a-10ef-7315-94f2-cbeec2f167d8');

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
        $validUntil = $this->clock->now()->add(new \DateInterval('P4M12D'));

        static::createClient()->request('PATCH', '/authentication/acme-inc/organizations/01966c5a-10ef-77a1-b158-d4356966e1ab/disable', [
            'json' => [
                'enabled' => false,
                'validUntil' => null,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
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
    public function itShouldDeleteAnOrganization(): void
    {
        static::createClient()->request('DELETE', '/authentication/acme-inc/organizations/01966c5a-10ef-77a1-b158-d4356966e1ab', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
