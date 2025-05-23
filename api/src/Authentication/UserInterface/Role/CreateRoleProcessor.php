<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\Command\UseCases\CreateEnabledRole;
use App\Authentication\Domain\Role\Command\UseCases\CreatePendingRole;
use App\Authentication\Domain\Role\Command\UseCases\CreateRole;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use App\Authentication\Domain\Role\RoleId;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CreateRoleProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private RoleRepositoryInterface $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Role
    {
        if (!$data instanceof CreateRoleInput) {
            throw new BadRequestHttpException();
        }

        try {
            if ($data instanceof CreateRoleWithinOrganizationInput
                && array_key_exists('organizationId', $uriVariables)
            ) {
                $organizationId = OrganizationId::fromString($uriVariables['organizationId']);

                $command = new CreateRole(
                    RoleId::generateRandom(),
                    $organizationId,
                    $data->identifier,
                    $data->label,
                    $data->resourceAccesses,
                );
            } else if ($data instanceof CreateRoleInput) {
                $command = new CreateRole(
                    RoleId::generateRandom(),
                    $data->organizationId,
                    $data->identifier,
                    $data->label,
                    $data->resourceAccesses,
                );
            } else {
                throw new BadRequestHttpException();
            }

            $this->commandBus->apply($command);
        } catch (NotFoundHttpException $exception) {
            throw new LogicException($exception->getMessage(), previous: $exception);
        }

        return $this->userRepository->get($command->uuid);
    }
}
