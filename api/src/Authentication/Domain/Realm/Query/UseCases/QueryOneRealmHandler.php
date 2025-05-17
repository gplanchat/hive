<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Realm\Query\UseCases;

use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Infrastructure\Keycloak\Keycloak;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QueryOneRealmHandler
{
    public function __construct(
        private Keycloak $keycloak,
    ) {
    }

    public function __invoke(QueryOneRealm $query): Realm
    {
        return $this->keycloak->queryOneRealm($query->code);
    }
}
