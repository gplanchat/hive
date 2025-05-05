<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Workspace;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\Workspace\Query\UseCases\WorkspacePage;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class QuerySeveralWorkspaceProvider implements ProviderInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        $type = $operation->getInput()['class'];
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($context, $type, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = $this->denormalizer->denormalize($context, $type, $request->getRequestFormat());

        $result = $this->queryBus->query($input);

        if (!$result instanceof WorkspacePage) {
            throw new LogicException();
        }

        return new TraversablePaginator(
            $result,
            $result->page,
            $result->pageSize,
            $result->totalItems,
        );
    }
}
