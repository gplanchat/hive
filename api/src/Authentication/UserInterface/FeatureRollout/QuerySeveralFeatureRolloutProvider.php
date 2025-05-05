<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\FeatureRollout;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\FeatureRollout\UseCases\FeatureRolloutPage;
use App\Authentication\Domain\FeatureRollout\UseCases\QuerySeveralFeatureRollout;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\QueryBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class QuerySeveralFeatureRolloutProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        $query = new QuerySeveralFeatureRollout(
            max((int) ($data['filters']['page'] ?? 1), 1),
            min(max((int) ($data['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 25), 10), $operation->getPaginationMaximumItemsPerPage()),
        );

        try {
            $result = $this->queryBus->query($query);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof FeatureRolloutPage) {
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
