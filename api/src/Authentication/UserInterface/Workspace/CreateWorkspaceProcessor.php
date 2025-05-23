<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Workspace;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\Command\UseCases\CreateEnabledWorkspace;
use App\Authentication\Domain\Workspace\Command\UseCases\CreatePendingWorkspace;
use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Domain\Workspace\Query\WorkspaceRepositoryInterface;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceInput;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceWithinOrganizationInput;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CreateWorkspaceProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private WorkspaceRepositoryInterface $workspaceRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Workspace
    {
        try {
            if ($data instanceof CreateWorkspaceWithinOrganizationInput
                && array_key_exists('organizationId', $uriVariables)
            ) {
                $organizationId = OrganizationId::fromString($uriVariables['organizationId']);

                $command = $data->enabled
                    ? new CreateEnabledWorkspace(
                        WorkspaceId::generateRandom(),
                        $organizationId,
                        $data->name,
                        $data->slug,
                        $data->validUntil,
                    )
                    : new CreatePendingWorkspace(
                        WorkspaceId::generateRandom(),
                        $organizationId,
                        $data->name,
                        $data->slug,
                    );
            } else if ($data instanceof CreateWorkspaceInput) {
                $command = $data->enabled
                    ? new CreateEnabledWorkspace(
                        WorkspaceId::generateRandom(),
                        $data->organizationId,
                        $data->name,
                        $data->slug,
                        $data->validUntil,
                    )
                    : new CreatePendingWorkspace(
                        WorkspaceId::generateRandom(),
                        $data->organizationId,
                        $data->name,
                        $data->slug,
                    );
            } else {
                throw new BadRequestHttpException();
            }

            $this->commandBus->apply($command);
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        return $this->workspaceRepository->get($command->uuid);
    }
}
