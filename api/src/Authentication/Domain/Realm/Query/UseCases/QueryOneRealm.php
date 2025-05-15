<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Realm\Query\UseCases;

use App\Authentication\Domain\Realm\RealmId;

final readonly class QueryOneRealm
{
    public function __construct(
        public RealmId $code,
    ) {
    }
}
