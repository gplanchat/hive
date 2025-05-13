<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\Role\Query\Role;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class KeycloakUser implements UserInterface
{
    /** @param Role[] $roles */
    public function __construct(
        private KeycloakUserId $keycloakUserId,
        private array $roles,
    ) {
        array_all($this->roles, fn ($role) => $role instanceof Role) || throw new \InvalidArgumentException();
    }

    public function getRoles(): array
    {
        return array_map(fn (Role $role) => $role->identifier, $this->roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->keycloakUserId->toString();
    }
}
