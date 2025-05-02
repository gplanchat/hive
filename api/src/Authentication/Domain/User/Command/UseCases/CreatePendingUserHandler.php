<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command\UseCases;

use App\Authentication\Domain\User\Command\User;
use App\Authentication\Domain\User\Command\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreatePendingUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(CreatePendingUser $command): void
    {
        $user = User::declareDisabled(
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
