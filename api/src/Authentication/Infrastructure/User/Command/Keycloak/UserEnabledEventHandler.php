<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command\Keycloak;

use App\Authentication\Domain\User\Command\EnabledEvent;
use App\Authentication\Domain\User\Command\UseCases\EnableUser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UserEnabledEventHandler
{
    public function __invoke(EnabledEvent $event): void
    {
    }
}
