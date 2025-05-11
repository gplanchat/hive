<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\SecurityContextInterface;
use App\Authentication\Domain\User\KeycloakUserId;

final class KeycloakSecurityContext implements SecurityContextInterface
{
    public function keycloakUserId(): KeycloakUserId
    {
        return KeycloakUserId::generateRandom();
    }
}
