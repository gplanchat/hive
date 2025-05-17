<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases;

use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccount;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccountRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QueryOneCloudProviderAccountHandler
{
    public function __construct(
        private CloudProviderAccountRepositoryInterface $cloudProviderAccountRepository,
    ) {
    }

    public function __invoke(QueryOneCloudProviderAccount $query): CloudProviderAccount
    {
        return $this->cloudProviderAccountRepository->get($query->uuid);
    }
}
