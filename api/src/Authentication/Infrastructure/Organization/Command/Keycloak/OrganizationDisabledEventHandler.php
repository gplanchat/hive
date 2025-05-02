<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command\Keycloak;

use App\Authentication\Domain\Organization\Command\DisabledEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class OrganizationDisabledEventHandler
{
    public function __invoke(DisabledEvent $event): void
    {
    }
}
