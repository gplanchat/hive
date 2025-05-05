<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command\Keycloak;

use App\Authentication\Domain\Organization\Command\DeclaredEvent;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Infrastructure\Keycloak\KeycloakInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'event.bus')]
final readonly class OrganizationDeclaredEventHandler
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private KeycloakInterface $keycloak,
    ) {
    }

    public function __invoke(DeclaredEvent $event): void
    {
        $organization = $this->organizationRepository->get($event->uuid);

        $this->keycloak->createRealmFromOrganization($organization);
    }
}
