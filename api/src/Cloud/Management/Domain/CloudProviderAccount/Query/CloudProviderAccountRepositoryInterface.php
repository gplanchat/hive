<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query;

use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases\CloudProviderAccountPage;
use App\Cloud\Management\Domain\NotFoundException;

interface CloudProviderAccountRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(CloudProviderAccountId $cloudProviderAccountId): CloudProviderAccount;

    public function list(int $currentPage = 1, int $pageSize = 25): CloudProviderAccountPage;
}
