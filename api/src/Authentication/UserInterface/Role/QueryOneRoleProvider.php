<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\Query\UseCases\QueryOneRole;
use App\Authentication\Domain\Role\RoleId;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class QueryOneRoleProvider implements ProviderInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Role
    {
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($uriVariables['uuid'], RoleId::class, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = new QueryOneRole(
            RoleId::fromString($uriVariables['uuid']),
        );

        try {
            $result = $this->queryBus->query($input);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof Role) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
