<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetSeveralRoleInOrganizationHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    public function __invoke(GetSeveralRoleInOrganization $query): RolePage
    {
        return $this->roleRepository->listFromOrganization($query->organizationId, $query->currentPage, $query->itemsPerPage);
    }
}
