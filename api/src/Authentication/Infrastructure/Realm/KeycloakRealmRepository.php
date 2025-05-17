<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Realm;

use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\Query\RealmRepositoryInterface;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;

final readonly class KeycloakRealmRepository implements RealmRepositoryInterface
{
    public function __construct(
        private KeycloakInterface $keycloak,
    ) {
    }

    public function get(RealmId $realmId): Realm
    {
        return $this->keycloak->queryOneRealm($realmId);
    }

    public function list(int $currentPage = 1, int $pageSize = 25): RealmPage
    {
        return $this->keycloak->queryAllRealms();
    }
}
