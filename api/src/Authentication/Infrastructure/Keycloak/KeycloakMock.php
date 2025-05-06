<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\User;

final class KeycloakMock implements KeycloakInterface
{
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

    public function fetchOpenidCertificates(Realm $realm): void
    {
    }
}
