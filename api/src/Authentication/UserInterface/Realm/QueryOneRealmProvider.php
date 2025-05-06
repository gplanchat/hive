<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Realm;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\Realm\Query\UseCases\QueryOneRealm;
use App\Authentication\Domain\Realm\RealmId;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class QueryOneRealmProvider implements ProviderInterface
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Realm
    {
        try {
            $query = new QueryOneRealm(
                RealmId::fromString($uriVariables['code']),
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        }

        try {
            $result = $this->queryBus->query($query);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof Realm) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
