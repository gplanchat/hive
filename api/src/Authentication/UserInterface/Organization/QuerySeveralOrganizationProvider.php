<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Query\UseCases\OrganizationPage;
use App\Authentication\Domain\QueryBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class QuerySeveralOrganizationProvider implements ProviderInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        $type = $operation->getInput()['class'];
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($context, $type, $request->getRequestFormat())) {
            throw new BadRequestHttpException();
        }

        $input = $this->denormalizer->denormalize($context, $type, $request->getRequestFormat());

        try {
            $result = $this->queryBus->query($input);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof OrganizationPage) {
            throw new BadRequestHttpException();
        }

        return new TraversablePaginator(
            $result,
            $result->page,
            $result->pageSize,
            $result->totalItems,
        );
    }
}
