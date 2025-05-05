<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

final readonly class QuerySeveralRole
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
