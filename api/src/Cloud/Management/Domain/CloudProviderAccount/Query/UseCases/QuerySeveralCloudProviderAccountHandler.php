<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases;

use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccountRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final class QuerySeveralCloudProviderAccountHandler
{
    public function __construct(
        private CloudProviderAccountRepositoryInterface $cloudProviderAccountRepository,
    ) {
    }

    public function __invoke(QuerySeveralCloudProviderAccount $query): CloudProviderAccountPage
    {
        return $this->cloudProviderAccountRepository->list($query->currentPage, $query->itemsPerPage);
    }
}
