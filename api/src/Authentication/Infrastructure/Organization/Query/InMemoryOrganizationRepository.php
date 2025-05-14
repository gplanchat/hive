<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\Organization\Query\UseCases\OrganizationPage;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\StorageMock;

final class InMemoryOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
    ) {
    }

    public function get(OrganizationId $organizationId, RealmId $realmId): Organization
    {
        $item = $this->storage->getItem(OrganizationFixtures::buildCacheKey($organizationId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof Organization) {
            throw new NotFoundException();
        }

        return $value;
    }

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): OrganizationPage
    {
        $result = $this->storage->getValues()
            ->filter(fn (mixed $value): bool => $value instanceof Organization)
            ->filter(fn (Organization $organization) => $organization->realmId->equals($realmId))
            ->toArray()
        ;

        return new OrganizationPage(
            $currentPage,
            $pageSize,
            \count($result),
            ...\array_slice(array_values($result), ($currentPage - 1) * $pageSize, $pageSize)
        );
    }
}
