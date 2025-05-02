<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCase;

use App\Authentication\Domain\User\Query\UserPage;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetSeveralUserInOrganizationHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(GetSeveralUserInOrganization $query): UserPage
    {
        return $this->userRepository->listFromOrganization($query->organizationId, $query->currentPage, $query->itemsPerPage);
    }
}
