<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\User\Query\User;

interface KeycloakInterface
{
    public function createRealmFromOrganization(Organization $organization): void;

    public function createUserInsideRealmFromUser(Organization $organization, User $user): void;
}
