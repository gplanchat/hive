<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure\Messenger;

use App\Platform\Infrastructure\EventBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final readonly class EventBus implements EventBusInterface
{
    public function __construct(
        #[Autowire('@event.bus')]
        private MessageBusInterface $messageBus,
    ) {
    }

    public function emit(object $event): void
    {
        $this->messageBus->dispatch(
            (new Envelope($event))
                ->with(new DispatchAfterCurrentBusStamp())
        );
    }
}
