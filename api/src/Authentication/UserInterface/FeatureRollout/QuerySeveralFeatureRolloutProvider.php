<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\FeatureRollout;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\FeatureRollout\UseCases\FeatureRolloutPage;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class QuerySeveralFeatureRolloutProvider implements ProviderInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        $type = $operation->getInput()['class'];
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($context, $type, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = $this->denormalizer->denormalize($context, $type, $request->getRequestFormat());

        $envelope = $this->messageBus->dispatch($input);

        $result = $envelope->last(HandledStamp::class)->getResult();

        if (!$result instanceof FeatureRolloutPage) {
            throw new LogicException();
        }

        return new TraversablePaginator(
            $result->getIterator(),
            $result->page,
            $result->pageSize,
            $result->totalItems,
        );
    }
}
