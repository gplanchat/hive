<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases;

final readonly class QuerySeveralCloudProviderAccount
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
