<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command\UseCases;

use App\Authentication\Domain\Role\Command\RoleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteRoleHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    public function __invoke(DeleteRole $command): void
    {
        $role = $this->roleRepository->get($command->roleId);

        $role->delete();

        $this->roleRepository->save($role);
    }
}
