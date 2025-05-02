<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\User\Query\User;

final readonly class Keycloak implements KeycloakInterface
{
    public function __construct(
        private KeycloakClientInterface $httpClient
    ) {}

    public function createRealmFromOrganization(
        Organization $organization,
    ): void {
        // FIXME: change Keycloak URI
        $response = $this->httpClient->request('POST', 'http://keycloak:7080/admin/realms', [
            'headers' => [
                'content-type' => 'application/json',
            ],
            'body' => \json_encode([
                'id' => $organization->uuid->toString(),
                'realm' => $organization->slug,
                'notBefore' => 0,
                'revokeRefreshToken' => false,
                'displayName' => $organization->name,
                'enabled' => $organization->enabled,
                'sslRequired' => 'external',
                'registrationAllowed' => false,
                'loginWithEmailAllowed' => true,
                'duplicateEmailsAllowed' => false,
                'resetPasswordAllowed' => false,
                'editUsernameAllowed' => false,
                'bruteForceProtected' => true,
            ], JSON_THROW_ON_ERROR),
        ]);

        if ($response->getStatusCode() !== 409) {
            throw new ConflictException(strtr(
                'Could not create Realm on the Keycloak instance %keycloakUri%, as another realm already uses this name.',
                [
                    // FIXME: change Keycloak URI
                    '%keycloakUri%' => 'http://keycloak:7080/',
                ]
            ));
        }

        if ($response->getStatusCode() !== 201) {
            throw new \RuntimeException(strtr(
                'Could not create Realm on the Keycloak instance %keycloakUri%',
                [
                    // FIXME: change Keycloak URI
                    '%keycloakUri%' => 'http://keycloak:7080/',
                ]
            ));
        }
    }

    public function createUserInsideRealmFromUser(
        Organization $organization,
        User $user,
    ): void {
        $url = strtr(
            'http://keycloak:7080/admin/realms/{realm}/users',
            [
                '{realm}' => $organization->slug,
            ]
        );

        // FIXME: change Keycloak URI
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'content-type' => 'application/json',
            ],
            'body' => \json_encode([
                'id' => $user->uuid->toString(),
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'enabled' => $user->enabled,
            ], JSON_THROW_ON_ERROR),
        ]);

        if ($response->getStatusCode() !== 201) {
            throw new \RuntimeException(strtr(
                'Could not create User in the %realm% on the Keycloak instance %keycloakUri%',
                [
                    // FIXME: change Keycloak URI
                    '%keycloakUri%' => 'http://keycloak:7080/',
                    '%realm%' => $organization->slug,
                ]
            ));
        }
    }
}
