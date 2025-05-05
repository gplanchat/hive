<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QuerySeveralRoleHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    public function __invoke(QuerySeveralRole $query): RolePage
    {
        return $this->roleRepository->list($query->currentPage, $query->itemsPerPage);
    }
}
