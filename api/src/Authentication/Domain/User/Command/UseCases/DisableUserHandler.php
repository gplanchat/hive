<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command\UseCases;

use App\Authentication\Domain\User\Command\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DisableUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(DisableUser $command): void
    {
        $user = $this->userRepository->get($command->uuid, $command->realmId);
        $user->disable();
        $this->userRepository->save($user);
    }
}
