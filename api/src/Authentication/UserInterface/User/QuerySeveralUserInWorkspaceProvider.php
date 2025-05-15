<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\UseCases\QuerySeveralUserInWorkspace;
use App\Authentication\Domain\User\Query\UseCases\UserPage;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * @implements ProviderInterface<User>
 */
final readonly class QuerySeveralUserInWorkspaceProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private Pagination $pagination,
    ) {
    }

    /**
     * @return PaginatorInterface<User>|TraversablePaginator
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        try {
            $query = new QuerySeveralUserInWorkspace(
                RealmId::fromString($uriVariables['realm']),
                WorkspaceId::fromString($uriVariables['workspaceId']),
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

        if (!$result instanceof UserPage) {
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
