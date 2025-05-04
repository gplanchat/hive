<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetOneUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetOneUser $query): User
    {
        return $this->userRepository->get($query->uuid);
    }
}
