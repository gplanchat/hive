<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use ApiPlatform\Symfony\Bundle\Test\ClientTrait;
use Psr\Clock\ClockInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class KeycloakAdminClient implements KeycloakAdminClientInterface
{
    use ClientTrait;

    private ?string $accessToken = null;
    private ?\DateTimeInterface $expiration = null;
    private ?string $refreshToken = null;
    private ?\DateTimeInterface $refreshExpiration = null;

    public function __construct(
        private HttpClientInterface $decorated,
        private ClockInterface $clock,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $options = array_merge($options, [
            'headers' => array_merge($options['headers'] ?? [], [
                'authorization' => "bearer {$this->authenticateWithAccessToken()}",
            ])
        ]);

        return $this->decorated->request($method, $url, $options);
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->decorated->stream($responses, $timeout);
    }

    private function authenticateWithAccessToken(): string
    {
        if ($this->expiration !== null && $this->clock->now() < $this->expiration) {
            return $this->accessToken;
        }

        $now = $this->clock->now();

        if ($this->refreshExpiration !== null && $this->clock->now() < $this->refreshExpiration) {
            // FIXME: change Keycloak URI
            $response = $this->decorated->request('POST', 'http://keycloak:7080/realms/master/protocol/openid-connect/token', [
                'body' => [
                    'client_id' => 'admin-cli',
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->refreshToken,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $this->storeRefreshToken($response, $now);

                return $this->accessToken;
            }
        }

        // FIXME: change Keycloak URI
        $response = $this->decorated->request('POST', 'http://keycloak:7080/realms/master/protocol/openid-connect/token', [
            'body' => [
                'client_id' => 'admin-cli',
                'grant_type' => 'password',
                'username' => 'admin',
                'password' => 'password',
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $this->storeRefreshToken($response, $now);

            return $this->accessToken;
        }

        $this->accessToken = null;
        $this->expiration = null;
        $this->refreshToken = null;
        $this->refreshExpiration = null;

        return $this->accessToken;
    }

    private function storeRefreshToken(ResponseInterface $response, \DateTimeInterface $now): void
    {
        $result = $response->toArray();

        $this->accessToken = $result['access_token'];
        $this->expiration = $this->expiration($now, $result['expires_in']);
        $this->refreshToken = $result['refresh_token'];
        $this->refreshExpiration = $this->expiration($now, $result['refresh_expires_in']);
    }

    private function expiration(\DateTimeInterface $now, int $seconds): ?\DateTimeInterface
    {
        if ($seconds <= 0) {
            return $now;
        }

        return $now->add(new \DateInterval("PT{$seconds}S"));
    }
}
