<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Command\UseCases\CreateRole;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use App\Authentication\Domain\Role\RoleId;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<CreateRoleInput, Role>
 */
final readonly class CreateRoleProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private RoleRepositoryInterface $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Role
    {
        try {
            $realmId = RealmId::fromString($uriVariables['realm']);

            if ($data instanceof CreateRoleWithinOrganizationInput
                && \array_key_exists('organizationId', $uriVariables)
            ) {
                $organizationId = OrganizationId::fromString($uriVariables['organizationId']);

                $command = new CreateRole(
                    RoleId::generateRandom(),
                    $realmId,
                    $organizationId,
                    $data->identifier,
                    $data->label,
                    $data->resourceAccesses,
                );
            } elseif ($data instanceof CreateRoleInput) {
                $command = new CreateRole(
                    RoleId::generateRandom(),
                    $realmId,
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

        return $this->userRepository->get($command->uuid, $command->realmId);
    }
}
