<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\Query\UseCases\QueryOneRole;
use App\Authentication\Domain\Role\RoleId;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final readonly class QueryOneRoleProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Role
    {
        try {
            $query = new QueryOneRole(
                RealmId::fromString($uriVariables['realm']),
                RoleId::fromString($uriVariables['uuid']),
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        }

        try {
            $result = $this->queryBus->query($query);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof Role) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
