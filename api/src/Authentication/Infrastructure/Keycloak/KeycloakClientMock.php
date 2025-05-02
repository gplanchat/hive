<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use Symfony\Component\HttpClient\MockHttpClient;

final class KeycloakClientMock extends MockHttpClient implements KeycloakClientInterface
{
}
