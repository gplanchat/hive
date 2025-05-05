<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query\UseCases;

final readonly class QuerySeveralWorkspace
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
