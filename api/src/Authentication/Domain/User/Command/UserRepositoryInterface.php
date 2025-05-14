<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command;

use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\UserId;

interface UserRepositoryInterface
{
    public function get(
        UserId $userId,
        RealmId $realmId,
    ): User;

    public function save(
        User $user,
    ): void;
}
