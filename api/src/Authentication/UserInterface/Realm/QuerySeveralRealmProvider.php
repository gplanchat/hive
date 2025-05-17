<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Realm;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\Query\UseCases\QuerySeveralRealm;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Platform\Infrastructure\QueryBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<Realm>
 */
final readonly class QuerySeveralRealmProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private Pagination $pagination,
    ) {
    }

    /**
     * @return PaginatorInterface<Realm>|TraversablePaginator
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        $query = new QuerySeveralRealm(
            $this->pagination->getPage($context),
            $this->pagination->getLimit($operation, $context),
        );

        try {
            $result = $this->queryBus->query($query);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof RealmPage) {
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
