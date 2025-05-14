<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\User;
use Firebase\JWT\JWT;
use Psr\Clock\ClockInterface;

final readonly class KeycloakMock implements KeycloakInterface
{
    public function __construct(
        private KeysRegistry $keysRegistry,
        private ClockInterface $clock,
    ) {
    }

    public static function create(ClockInterface $clock): self
    {
        return new self(KeysRegistry::create(), $clock);
    }

    public function createRealm(Realm $realm): void
    {
    }

    public function queryAllRealms(): RealmPage
    {
        return new RealmPage(
            1,
            25,
            1,
            new Realm(
                RealmId::fromString('acme-inc'),
                'ACME Inc.',
            )
        );
    }

    public function queryOneRealm(RealmId $realmId): Realm
    {
        if (!$realmId->equals('acme-inc')) {
            throw new \RuntimeException();
        }

        return new Realm(
            RealmId::fromString('acme-inc'),
            'ACME Inc.',
        );
    }

    public function createOrganizationInsideRealm(RealmId $realmId, Organization $organization): void
    {
    }

    public function createUserInsideRealm(Realm $realm, User $user): void
    {
    }

    public function fetchOpenidCertificates(RealmId $realmId): array
    {
        return $this->keysRegistry->publicKeys()->toArray();
    }

    public function generateJWT(string $subject, int $keyId = 0, array $payload = [], array $headers = [], \DateInterval $expiration = new \DateInterval('PT1H')): string
    {
        $certificates = $this->keysRegistry->privateKeys()->toArray();

        return JWT::encode(
            [
                ...[
                    'exp' => $this->clock->now()->add($expiration)->getTimestamp(),
                    'nbf' => $this->clock->now()->getTimestamp(),
                    'sub' => $subject,
                ],
                ...$payload,
            ],
            $certificates[$keyId]->getKeyMaterial(),
            $certificates[$keyId]->getAlgorithm(),
            \sprintf('%d', $keyId),
            $headers,
        );
    }
}
