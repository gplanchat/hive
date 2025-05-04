<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Command\Keycloak;

use App\Authentication\Domain\Role\Command\DeclaredEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RoleDeclaredEventHandler
{
    public function __invoke(DeclaredEvent $event): void
    {
    }
}
