<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\UserId;

final readonly class QueryOneUser
{
    public function __construct(
        public UserId $uuid,
        public RealmId $realmId,
    ) {
    }
}
