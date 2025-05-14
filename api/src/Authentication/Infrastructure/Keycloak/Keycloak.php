<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\User;
use Firebase\JWT\JWK;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriInterface;

final readonly class Keycloak implements KeycloakInterface
{
    /** @var RealmId[] */
    private array $allowedRealmIds;

    public function __construct(
        private KeycloakAdminClientInterface $httpClient,
        private UriInterface $baseUri,
        RealmId ...$allowedRealmIds,
    ) {
        $this->allowedRealmIds = $allowedRealmIds;
    }

    public static function createFromUri(
        KeycloakAdminClientInterface $httpClient,
        string $baseUri,
        array $allowedRealmIds,
    ): self {
        return new self(
            $httpClient,
            Psr17FactoryDiscovery::findUriFactory()->createUri($baseUri),
            ...array_map(fn (string $realmId) => RealmId::fromString($realmId), $allowedRealmIds),
        );
    }

    public function queryAllRealms(): RealmPage
    {
        $response = $this->httpClient->request('GET', "{$this->baseUri}/admin/realms", [
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(strtr('Could not list Realms on the Keycloak instance %keycloakUri%', ['%keycloakUri%' => $this->baseUri]));
        }

        $items = array_map(
            fn (array $current) => new Realm(
                code: RealmId::fromString($current['realm']),
                displayName: $current['displayName'],
            ),
            array_filter(
                $response->toArray(),
                fn (array $current): bool => array_any($this->allowedRealmIds, fn (RealmId $realmId) => $realmId->equals($current['realm']))
            ),
        );

        return new RealmPage(1, 100, \count($items), ...$items);
    }

    public function createOrganizationInsideRealm(RealmId $realmId, Organization $organization): void
    {
        if (!array_any($this->allowedRealmIds, fn (RealmId $current) => $current->equals($realmId))) {
            throw new \RuntimeException();
        }

        throw new \RuntimeException('Not implemented.');
    }

    public function queryOneRealm(RealmId $realmId): Realm
    {
        if (!array_any($this->allowedRealmIds, fn (RealmId $current) => $current->equals($realmId))) {
            throw new \RuntimeException();
        }

        $url = strtr(
            "{$this->baseUri}/admin/realms/{realm}",
            [
                '{realm}' => $realmId->toString(),
            ]
        );

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(strtr('Could not list Realms on the Keycloak instance %keycloakUri%', ['%keycloakUri%' => $this->baseUri]));
        }

        $item = $response->toArray();

        return new Realm(
            code: RealmId::fromString($item['realm']),
            displayName: $item['displayName'],
        );
    }

    public function createRealm(
        Realm $realm,
    ): void {
        $response = $this->httpClient->request('POST', "{$this->baseUri}/admin/realms", [
            'headers' => [
                'content-type' => 'application/json',
            ],
            'body' => json_encode([
                'id' => $realm->code->toString(),
                'realm' => $realm->slug,
                'notBefore' => 0,
                'revokeRefreshToken' => false,
                'displayName' => $realm->name,
                'enabled' => $realm->enabled,
                'sslRequired' => 'external',
                'registrationAllowed' => false,
                'loginWithEmailAllowed' => true,
                'duplicateEmailsAllowed' => false,
                'resetPasswordAllowed' => false,
                'editUsernameAllowed' => false,
                'bruteForceProtected' => true,
            ], \JSON_THROW_ON_ERROR),
        ]);

        if (409 !== $response->getStatusCode()) {
            throw new ConflictException(strtr('Could not create Realm on the Keycloak instance %keycloakUri%, as another realm already uses this name.', ['%keycloakUri%' => $this->baseUri]));
        }

        if (201 !== $response->getStatusCode()) {
            throw new \RuntimeException(strtr('Could not create Realm on the Keycloak instance %keycloakUri%', ['%keycloakUri%' => $this->baseUri]));
        }
    }

    public function createUserInsideRealm(
        Realm $realm,
        User $user,
    ): void {
        $url = strtr(
            "{$this->baseUri}/admin/realms/{realm}/users",
            [
                '{realm}' => $realm->code->toString(),
            ]
        );

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'content-type' => 'application/json',
            ],
            'body' => json_encode([
                'id' => $user->uuid->toString(),
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'enabled' => $user->enabled,
            ], \JSON_THROW_ON_ERROR),
        ]);

        if (201 !== $response->getStatusCode()) {
            throw new \RuntimeException(strtr('Could not create User in the %realm% on the Keycloak instance %keycloakUri%', ['%keycloakUri%' => $this->baseUri, '%realm%' => $realm->code->toString()]));
        }
    }

    public function fetchOpenidCertificates(RealmId $realmId): array
    {
        $url = strtr(
            "{$this->baseUri}/realms/{realm}/protocol/openid-connect/certs",
            [
                '{realm}' => $realmId->toString(),
            ]
        );

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(strtr('Could not create User in the %realm% on the Keycloak instance %keycloakUri%', ['%keycloakUri%' => $this->baseUri, '%realm%' => $realmId->toString()]));
        }

        return JWK::parseKeySet($response->toArray(false));
    }
}
