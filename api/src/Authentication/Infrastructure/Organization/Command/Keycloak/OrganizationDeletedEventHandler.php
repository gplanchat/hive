<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command\Keycloak;

use App\Authentication\Domain\Organization\Command\DeletedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'event.bus')]
final class OrganizationDeletedEventHandler
{
    public function __invoke(DeletedEvent $event): void
    {
    }
}
