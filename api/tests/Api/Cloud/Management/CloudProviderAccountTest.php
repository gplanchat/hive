<?php

declare(strict_types=1);

namespace App\Tests\Api\Cloud\Management;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;
use App\Authentication\Infrastructure\Keycloak\KeycloakMock;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;

/**
 * @internal
 *
 * @coversNothing
 */
final class CloudProviderAccountTest extends ApiTestCase
{
    public static ?bool $alwaysBootKernel = false;

    private ?UserFixtures $userFixtures = null;
    private ?RoleFixtures $roleFixtures = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $storageMock = self::getContainer()->get(StorageMock::class);
        \assert($storageMock instanceof StorageMock);

        $this->roleFixtures = new RoleFixtures($storageMock);
        \assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->load();

        $this->userFixtures = new UserFixtures($storageMock);
        \assert($this->userFixtures instanceof UserFixtures);
        $this->userFixtures->load();
    }

    protected function tearDown(): void
    {
        \assert($this->userFixtures instanceof UserFixtures);
        $this->userFixtures->unload();
        $this->userFixtures = null;

        \assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->unload();
        $this->roleFixtures = null;

        parent::tearDown();
    }

    private static function getTokenFor(string $username): string
    {
        $keycloak = self::getContainer()->get(KeycloakInterface::class);
        \assert($keycloak instanceof KeycloakMock);

        return $keycloak->generateJWT($username);
    }

    /** @test */
    public function itShouldListCloudProviderAccounts(): void
    {
        self::createClient()->request('GET', '/cloud/cloud-provider-accounts', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/CloudProviderAccount',
            '@id' => '/cloud/cloud-provider-accounts',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 2,
        ]);
    }

    /** @test */
    public function itShouldShowACloudProviderAccount(): void
    {
        self::createClient()->request('GET', '/cloud/cloud-provider-accounts/0196db78-95ad-7017-8b1e-0b8d55f65f9e', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/CloudProviderAccount',
            '@type' => 'CloudProviderAccount',
            '@id' => '/cloud/cloud-provider-accounts/0196db78-95ad-7017-8b1e-0b8d55f65f9e',
            'uuid' => '0196db78-95ad-7017-8b1e-0b8d55f65f9e',
            'type' => 'ovh-cloud',
            'name' => 'OVHCloud Gyroscops Europe',
            'description' => 'OVHCloud Gyroscops Europe',
            'featureRolloutIds' => [],
        ]);
    }
}
