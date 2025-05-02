<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\UseCases\GetOneOrganization;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GetOneOrganizationProvider implements ProviderInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Organization
    {
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($uriVariables['uuid'], OrganizationId::class, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = new GetOneOrganization(
            OrganizationId::fromString($uriVariables['uuid']),
        );

        try {
            $envelope = $this->messageBus->dispatch($input);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        $result = $envelope->last(HandledStamp::class)->getResult();

        if (!$result instanceof Organization) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
