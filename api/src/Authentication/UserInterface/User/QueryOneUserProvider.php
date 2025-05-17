<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Query\UseCases\QueryOneUser;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\UserId;
use App\Platform\Infrastructure\QueryBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * @implements ProviderInterface<User>
 */
final readonly class QueryOneUserProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): User
    {
        try {
            $query = new QueryOneUser(
                UserId::fromString($uriVariables['uuid']),
                RealmId::fromString($uriVariables['realm']),
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        }

        try {
            $result = $this->queryBus->query($query);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof User) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
