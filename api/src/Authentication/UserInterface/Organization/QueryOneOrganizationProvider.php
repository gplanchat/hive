<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\UseCases\QueryOneOrganization;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\Realm\RealmId;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * @implements ProviderInterface<Organization>
 */
final readonly class QueryOneOrganizationProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Organization
    {
        try {
            $query = new QueryOneOrganization(
                OrganizationId::fromString($uriVariables['uuid']),
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

        if (!$result instanceof Organization) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
