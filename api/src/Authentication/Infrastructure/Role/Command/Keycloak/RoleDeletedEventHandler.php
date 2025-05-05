<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Command\Keycloak;

use App\Authentication\Domain\Role\Command\DeletedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'event.bus')]
final class RoleDeletedEventHandler
{
    public function __invoke(DeletedEvent $event): void
    {
    }
}
