<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command\Keycloak;

use App\Authentication\Domain\Realm\Query\RealmRepositoryInterface;
use App\Authentication\Domain\User\Command\DeclaredEvent;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'event.bus')]
final readonly class UserCreatedEventHandler
{
    public function __construct(
        private RealmRepositoryInterface $realmRepository,
        private UserRepositoryInterface $userRepository,
        private KeycloakInterface $keycloak,
    ) {
    }

    public function __invoke(DeclaredEvent $event): void
    {
        $organization = $this->realmRepository->get($event->realmId);
        $user = $this->userRepository->get($event->uuid, $event->realmId);

        $this->keycloak->createUserInsideRealm($organization, $user);
    }
}
