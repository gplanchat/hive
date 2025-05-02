<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

final readonly class GetSeveralRole
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
