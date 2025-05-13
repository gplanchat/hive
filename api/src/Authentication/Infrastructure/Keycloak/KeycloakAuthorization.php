<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\User\AuthorizationInterface;

final readonly class KeycloakAuthorization implements AuthorizationInterface
{
    public function __construct(
        public KeycloakUserId $keycloakUserId,
    ) {
    }

    public static function fromNormalized(array $normalized): self
    {
        return new self(
            keycloakUserId::fromString($normalized['keycloakUserId']),
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'keycloakUserId' => $this->keycloakUserId->toString(),
        ];
    }
}
