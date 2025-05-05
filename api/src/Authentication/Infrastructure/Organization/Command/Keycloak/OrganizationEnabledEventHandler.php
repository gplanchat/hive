<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command\Keycloak;

use App\Authentication\Domain\Organization\Command\EnabledEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'event.bus')]
final class OrganizationEnabledEventHandler
{
    public function __invoke(EnabledEvent $event): void
    {
    }
}
