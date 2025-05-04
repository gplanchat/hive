<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Workspace;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\Workspace\Workspace;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GetOneWorkspaceProvider implements ProviderInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Workspace
    {
        $type = $operation->getInput()['class'];
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($context, $type, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = $this->denormalizer->denormalize($context, $type, $request->getRequestFormat());

        $envelope = $this->messageBus->dispatch($input);

        $result = $envelope->last(HandledStamp::class)->getResult();

        if (!$result instanceof Workspace) {
            throw new LogicException();
        }

        return $result;
    }
}
