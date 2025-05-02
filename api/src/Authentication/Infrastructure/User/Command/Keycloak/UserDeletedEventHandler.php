<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command\Keycloak;

use App\Authentication\Domain\User\Command\DeletedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UserDeletedEventHandler
{
    public function __invoke(DeletedEvent $event): void
    {
    }
}
