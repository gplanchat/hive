<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command\Keycloak;

use App\Authentication\Domain\User\Command\DisabledEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UserDisabledEventHandler
{
    public function __invoke(DisabledEvent $event): void
    {
    }
}
