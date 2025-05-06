<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\User;

interface KeycloakInterface
{
    public function createRealm(Realm $realm): void;
    public function queryAllRealms(): RealmPage;
    public function queryOneRealm(RealmId $realmId): Realm;
    public function createOrganizationInsideRealm(RealmId $realmId, Organization $organization): void;

    public function createUserInsideRealm(Realm $realm, User $user): void;

    public function fetchOpenidCertificates(Realm $realm): void;
}
