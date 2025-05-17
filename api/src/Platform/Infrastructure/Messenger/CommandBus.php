<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure\Messenger;

use App\Platform\Infrastructure\CommandBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CommandBus implements CommandBusInterface
{
    public function __construct(
        #[Autowire('@command.bus')]
        private MessageBusInterface $messageBus,
    ) {
    }

    public function apply(object $command): void
    {
        $this->messageBus->dispatch(
            new Envelope($command)
        );
    }
}
