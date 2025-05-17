<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\Query\UseCases\QuerySeveralRole;
use App\Authentication\Domain\Role\Query\UseCases\RolePage;
use App\Platform\Infrastructure\QueryBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * @implements ProviderInterface<Role>
 */
final readonly class QuerySeveralRoleProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private Pagination $pagination,
    ) {
    }

    /**
     * @return PaginatorInterface<Role>|TraversablePaginator
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        try {
            $query = new QuerySeveralRole(
                RealmId::fromString($uriVariables['realm']),
                $this->pagination->getPage($context),
                $this->pagination->getLimit($operation, $context),
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        }

        try {
            $result = $this->queryBus->query($query);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof RolePage) {
            throw new UnprocessableEntityHttpException();
        }

        return new TraversablePaginator(
            $result,
            $result->page,
            $result->pageSize,
            $result->totalItems,
        );
    }
}
