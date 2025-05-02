<?php

namespace App\Authentication\Domain\User\Command;

use App\Authentication\Domain\User\UserId;

interface UserRepositoryInterface
{
    public function get(
        UserId $userId,
    ): User;

    public function save(
        User $user,
    ): void;
}
