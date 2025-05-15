<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command\UseCases;

use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\UserId;

final readonly class EnableUser
{
    public function __construct(
        public UserId $uuid,
        public RealmId $realmId,
    ) {
    }
}
