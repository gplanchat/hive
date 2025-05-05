<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command\UseCases;

use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\User\Command\User;
use App\Authentication\Domain\User\Command\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateEnabledUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private OrganizationRepositoryInterface $organizationRepository,
    ) {}

    public function __invoke(CreateEnabledUser $command): void
    {
        $this->organizationRepository->get($command->organizationId);

        $user = User::declareEnabled(
            $command->uuid,
            $command->organizationId,
            $command->workspaceIds,
            $command->roleIds,
            $command->username,
            $command->firstName,
            $command->lastName,
            $command->email,
        );
        $this->userRepository->save($user);
    }
}
