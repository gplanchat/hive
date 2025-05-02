<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\Organization\Query\UseCases\OrganizationPage;
use App\Authentication\Infrastructure\StorageMock;

final class InMemoryOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(OrganizationId $organizationId): Organization
    {
        $item = $this->storage->getItem("tests.data-fixtures.organization.{$organizationId->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof Organization) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function list(int $currentPage = 1, int $pageSize = 25): OrganizationPage
    {
        $result = array_filter(
            $this->storage->getValues(),
            function (mixed $value): bool {
                return $value instanceof Organization;
            }
        );

        return new OrganizationPage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice(array_values($result), ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
