<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Workspace;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\Workspace\Query\UseCases\QueryOneWorkspace;
use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class QueryOneWorkspaceProvider implements ProviderInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Workspace
    {
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($uriVariables['uuid'], WorkspaceId::class, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = new QueryOneWorkspace(
            WorkspaceId::fromString($uriVariables['uuid']),
        );

        try {
            $result = $this->queryBus->query($input);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof Workspace) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
