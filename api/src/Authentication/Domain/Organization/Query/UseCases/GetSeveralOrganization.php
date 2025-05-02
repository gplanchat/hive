<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Query\UseCases;

final readonly class GetSeveralOrganization
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
