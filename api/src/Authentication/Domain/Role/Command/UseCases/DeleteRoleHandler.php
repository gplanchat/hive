<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command\UseCases;

use App\Authentication\Domain\Role\Command\RoleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteRoleHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function __invoke(DeleteRole $command): void
    {
        $role = $this->roleRepository->get($command->roleId, $command->realmId);

        $role->delete();

        $this->roleRepository->save($role);
    }
}
