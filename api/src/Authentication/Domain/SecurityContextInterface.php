<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use App\Authentication\Domain\User\KeycloakUserId;

interface SecurityContextInterface
{
    public function keycloakUserId(): KeycloakUserId;
}
