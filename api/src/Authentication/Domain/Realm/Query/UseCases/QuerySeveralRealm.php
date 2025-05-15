<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Realm\Query\UseCases;

final readonly class QuerySeveralRealm
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
