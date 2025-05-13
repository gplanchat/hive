<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;
use App\Authentication\Infrastructure\Keycloak\KeycloakMock;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;
use Psr\Clock\ClockInterface;

class FeatureRolloutsTest extends ApiTestCase
{
    static ?bool $alwaysBootKernel = false;

    private ?UserFixtures $userFixtures = null;
    private ?RoleFixtures $roleFixtures = null;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

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
    }

    protected function tearDown(): void
    {
        $this->userFixtures->unload();
        $this->userFixtures = null;

        $this->roleFixtures->unload();
        $this->roleFixtures = null;

        parent::tearDown();
    }

    private static function getTokenFor(string $username): string
    {
        $keycloak = self::getContainer()->get(KeycloakInterface::class);
        assert($keycloak instanceof KeycloakMock);

        return $keycloak->generateJWT($username);
    }

    /** @test */
    public function itShouldListFeatureRollouts(): void
    {
        static::createClient()->request('GET', '/feature-rollouts', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/FeatureRollout',
            '@id' => '/feature-rollouts',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 37,
        ]);
    }

    /** @test */
    public function itShouldShowAFeatureRollout(): void
    {
        static::createClient()->request('GET', '/feature-rollouts/subscription.enterprise', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/FeatureRollout',
            '@type' => 'FeatureRollout',
            '@id' => '/feature-rollouts/subscription.enterprise',
            'code' => 'subscription.enterprise',
        ]);
    }
}
