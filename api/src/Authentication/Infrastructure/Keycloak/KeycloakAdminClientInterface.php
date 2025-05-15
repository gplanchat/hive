<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface KeycloakAdminClientInterface extends HttpClientInterface
{
}
