<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases;

use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;

final readonly class QueryOneCloudProviderAccount
{
    public function __construct(
        public CloudProviderAccountId $uuid,
    ) {
    }
}
