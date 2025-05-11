<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QuerySeveralRoleInOrganizationHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    public function __invoke(QuerySeveralRoleInOrganization $query): RolePage
    {
        return $this->roleRepository->listFromOrganization($query->realmId, $query->organizationId, $query->currentPage, $query->itemsPerPage);
    }
}
