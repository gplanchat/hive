<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\SecurityContextInterface;

final class KeycloakSecurityContext implements SecurityContextInterface
{
    public function authorization(): KeycloakAuthorization
    {
        return new KeycloakAuthorization(
            KeycloakUserId::generateRandom(),
        );
    }
}
