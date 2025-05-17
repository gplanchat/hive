<?php

declare(strict_types=1);

namespace App\Tests\Api\Authentication;

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
final class RealmTest extends ApiTestCase
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
        \assert($this->roleFixtures instanceof RoleFixtures);
        $this->roleFixtures->unload();
        $this->roleFixtures = null;

        \assert($this->userFixtures instanceof UserFixtures);
        $this->userFixtures->unload();
        $this->userFixtures = null;

        parent::tearDown();
    }

    private static function getTokenFor(string $username): string
    {
        $keycloak = self::getContainer()->get(KeycloakInterface::class);
        \assert($keycloak instanceof KeycloakMock);

        return $keycloak->generateJWT($username);
    }

    /** @test */
    public function itShouldListRealms(): void
    {
        self::createClient()->request('GET', '/realms', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Realm',
            '@id' => '/realms',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 2,
        ]);
    }

    /** @test */
    public function itShouldShowAnRealm(): void
    {
        self::createClient()->request('GET', '/realms/acme-inc', [
            'headers' => [
                'authorization' => 'Bearer '.self::getTokenFor('/authentication/acme-inc/users/01966c5a-10ef-7abd-9c88-52b075bcae99'),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Realm',
            '@type' => 'Realm',
            '@id' => '/realms/acme-inc',
            'code' => 'acme-inc',
            'displayName' => 'acme-inc',
        ]);
    }
}
