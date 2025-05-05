<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Messenger;

use App\Authentication\Domain\QueryBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class QueryBus implements QueryBusInterface
{
    public function __construct(
        #[Autowire('@query.bus')]
        private MessageBusInterface $messageBus,
    ) {}

    public function query(object $query): object
    {
        $envelope = $this->messageBus->dispatch(
            (new Envelope($query))
        );

        return $envelope->last(HandledStamp::class)->getResult();
    }
}
