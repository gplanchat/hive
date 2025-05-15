<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

use App\Authentication\Domain\Realm\RealmId;

final readonly class QuerySeveralUser
{
    public function __construct(
        public RealmId $realmId,
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
