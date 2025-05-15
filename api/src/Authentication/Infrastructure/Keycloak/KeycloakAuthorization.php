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

    /**
     * @param array{keycloakUserId: non-empty-string} $normalized
     */
    public static function fromNormalized(array $normalized): self
    {
        return new self(
            KeycloakUserId::fromString($normalized['keycloakUserId']),
        );
    }

    /**
     * @return array{keycloakUserId: string}
     */
    public function jsonSerialize(): mixed
    {
        return [
            'keycloakUserId' => $this->keycloakUserId->toString(),
        ];
    }
}
