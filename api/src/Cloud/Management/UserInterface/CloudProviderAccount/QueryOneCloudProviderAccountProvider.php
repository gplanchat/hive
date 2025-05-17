<?php

declare(strict_types=1);

namespace App\Cloud\Management\UserInterface\CloudProviderAccount;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccount;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases\QueryOneCloudProviderAccount;
use App\Platform\Infrastructure\QueryBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * @implements ProviderInterface<CloudProviderAccount>
 */
final readonly class QueryOneCloudProviderAccountProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CloudProviderAccount
    {
        try {
            $query = new QueryOneCloudProviderAccount(
                CloudProviderAccountId::fromString($uriVariables['uuid']),
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        }

        try {
            $result = $this->queryBus->query($query);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof CloudProviderAccount) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
