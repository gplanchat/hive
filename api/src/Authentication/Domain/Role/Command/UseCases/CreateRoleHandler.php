<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command\UseCases;

use App\Authentication\Domain\Role\Command\Role;
use App\Authentication\Domain\Role\Command\RoleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateRoleHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function __invoke(CreateRole $command): void
    {
        $role = Role::declare(
            $command->uuid,
            $command->organizationId,
            $command->realmId,
            $command->identifier,
            $command->label,
            $command->resourceAccesses,
        );

        $this->roleRepository->save($role);
    }
}
