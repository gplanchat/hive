<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCase;

use App\Authentication\Domain\User\Query\UserPage;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetSeveralUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetSeveralUser $query): UserPage
    {
        return $this->userRepository->list($query->currentPage, $query->itemsPerPage);
    }
}
