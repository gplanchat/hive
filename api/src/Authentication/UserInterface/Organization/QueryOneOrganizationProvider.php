<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\UseCases\QueryOneOrganization;
use App\Authentication\Domain\QueryBusInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class QueryOneOrganizationProvider implements ProviderInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Organization
    {
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($uriVariables['uuid'], OrganizationId::class, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = new QueryOneOrganization(
            OrganizationId::fromString($uriVariables['uuid']),
        );

        try {
            $result = $this->queryBus->query($input);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof Organization) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
