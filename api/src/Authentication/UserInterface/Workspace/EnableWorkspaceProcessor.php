<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Workspace;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Workspace\Command\InvalidWorkspaceStateException;
use App\Authentication\Domain\Workspace\Command\UseCases\EnableWorkspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\Query\WorkspaceRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class EnableWorkspaceProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Workspace
    {
        if (!$data instanceof EnableWorkspaceInput) {
            throw new BadRequestHttpException();
        }

        try {
            $command = new EnableWorkspace(
                WorkspaceId::fromString($uriVariables['uuid']),
                $data->validUntil,
            );
            $this->commandBus->apply($command);
        } catch (InvalidWorkspaceStateException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        return $this->workspaceRepository->get($command->uuid);
    }
}
