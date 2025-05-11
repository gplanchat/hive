<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\RoleId;

final readonly class QueryOneRole
{
    public function __construct(
        public RoleId $uuid,
        public RealmId $realmId,
    ) {
    }
}
