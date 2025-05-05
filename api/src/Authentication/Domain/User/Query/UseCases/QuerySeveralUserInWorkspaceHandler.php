<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

use App\Authentication\Domain\User\Query\UserPage;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QuerySeveralUserInWorkspaceHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(QuerySeveralUserInWorkspace $query): UserPage
    {
        return $this->userRepository->listFromWorkspace($query->workspaceId, $query->currentPage, $query->itemsPerPage);
    }
}
