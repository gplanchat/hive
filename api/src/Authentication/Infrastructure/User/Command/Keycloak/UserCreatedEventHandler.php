<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command\Keycloak;

use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\User\Command\DeclaredEvent;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UserCreatedEventHandler
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private UserRepositoryInterface $userRepository,
        private KeycloakInterface $keycloak,
    ) {
    }

    public function __invoke(DeclaredEvent $event): void
    {
        $organization = $this->organizationRepository->get($event->organizationId);
        $user = $this->userRepository->get($event->uuid);

        $this->keycloak->createUserInsideRealmFromUser($organization, $user);
    }
}
